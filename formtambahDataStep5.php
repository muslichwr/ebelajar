<?php
/**
 * Backend handler for Syntax 5 (Project Submission) - Saves project data with file upload
 * Uses Moodle File Storage API for secure file handling
 * 
 * Triangle of Consistency:
 * - Component: mod_ebelajar
 * - File Area: product_evidence
 * - Item ID: $project->id
 */

require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB, $USER;

    $group_project = required_param('group_project', PARAM_INT);
    $cmid = required_param('cmid', PARAM_INT);
    $description = optional_param('product_description', '', PARAM_RAW);
    $youtube_link = optional_param('youtube_link', '', PARAM_URL);

    // Get course module record
    $cm = get_coursemodule_from_id('ebelajar', $cmid, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $context = context_module::instance($cm->id);

    // Get the project record (filter by BOTH ebelajar AND group_project for data isolation)
    $project = $DB->get_record('project', [
        'ebelajar' => $cm->instance,
        'group_project' => $group_project
    ]);

    if (!$project) {
        echo "Error: Project record tidak ditemukan.";
        exit;
    }

    $filename = null;
    $file_saved = false;

    // Handle file upload using Moodle File Storage API
    if (isset($_FILES['product_file']) && $_FILES['product_file']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['product_file']['name'];
        $file_tmp = $_FILES['product_file']['tmp_name'];
        
        // Validate file extension
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            echo "Error: Format file tidak diizinkan. Gunakan: " . implode(', ', $allowed_extensions);
            exit;
        }

        // Get file storage
        $fs = get_file_storage();

        // Delete existing files in this area for this project (replace mode)
        $fs->delete_area_files($context->id, 'mod_ebelajar', 'product_evidence', $project->id);

        // Prepare file info following "Triangle of Consistency"
        $fileinfo = [
            'contextid' => $context->id,
            'component' => 'mod_ebelajar',
            'filearea' => 'product_evidence',
            'itemid' => $project->id,
            'filepath' => '/',
            'filename' => $file_name,
            'userid' => $USER->id,
            'timecreated' => time(),
            'timemodified' => time()
        ];

        // Save file to Moodle file storage
        $stored_file = $fs->create_file_from_pathname($fileinfo, $file_tmp);
        
        if ($stored_file) {
            $filename = $file_name;
            $file_saved = true;
        }
    }

    // Preserve existing data when editing (if no new file uploaded)
    $existing_data = [];
    if (!empty($project->product_data)) {
        $existing_data = json_decode($project->product_data, true);
    }
    
    // If no new file uploaded, keep the old filename
    if ($filename === null && !empty($existing_data['filename'])) {
        $filename = $existing_data['filename'];
    }

    // Build JSON metadata for product_data column
    $product_data = [
        'description' => $description,
        'youtube_link' => $youtube_link,
        'filename' => $filename,
        'uploaded_at' => date('c'), // ISO 8601 format
        'uploaded_by' => $USER->id
    ];

    // Update project record
    $record = new stdClass();
    $record->id = $project->id;
    $record->group_project = $group_project;
    $record->product_data = json_encode($product_data);
    $record->status_step5 = "Selesai";
    $record->status_step6 = "Mengerjakan";
    $record->updated_at = time();

    if ($DB->update_record('project', $record)) {
        echo "Data proyek berhasil disimpan!";
    } else {
        echo "Error: Gagal menyimpan data proyek.";
    }

} else {
    print_error("Permintaan tidak valid!");
}
?>
