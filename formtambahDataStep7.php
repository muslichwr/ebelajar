<?php
require_once('../../config.php');
require_login();

$group_project = required_param('group_project', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$revision_notes = optional_param('revision_notes', '', PARAM_TEXT);

$cm = get_coursemodule_from_id('ebelajar', $cmid, 0, false, MUST_EXIST);
$context = context_module::instance($cm->id);

// Data Isolation: Filter by both instance and group
$project = $DB->get_record('project', [
    'ebelajar' => $cm->instance,
    'group_project' => $group_project
]);

if (!$project) {
    die('Error: Project not found');
}

// Merge Logic: Read existing data first
$data = [];
if (!empty($project->evaluation_data)) {
    $data = json_decode($project->evaluation_data, true);
    if (!is_array($data)) {
        $data = []; // Reset if corrupted
    }
}

// Update Student Parts (Merge into existing)
$data['revision_notes'] = $revision_notes;

// Handle File Upload
$fs = get_file_storage();
$filearea = 'revision_file';
$itemid = $project->id;

if (isset($_FILES['revision_file']) && $_FILES['revision_file']['error'] === UPLOAD_ERR_OK) {
    // Delete old file if exists (clean up before new upload)
    $fs->delete_area_files($context->id, 'mod_ebelajar', $filearea, $itemid);
    
    $fileinfo = [
        'contextid' => $context->id,
        'component' => 'mod_ebelajar',
        'filearea' => $filearea,
        'itemid' => $itemid,
        'filepath' => '/',
        'filename' => $_FILES['revision_file']['name']
    ];
    
    $fs->create_file_from_pathname($fileinfo, $_FILES['revision_file']['tmp_name']);
    
    // Save filename to JSON
    $data['revision_file'] = $_FILES['revision_file']['name'];
} 
// If no new file uploaded, we implicitly keep the existing $data['revision_file'] 
// because we decoded the full object above.

$record = new stdClass();
$record->id = $project->id;
$record->evaluation_data = json_encode($data);
$record->status_step7 = 'Selesai';
$record->updated_at = time();

if ($DB->update_record('project', $record)) {
    echo "Success";
} else {
    echo "Error: Failed to update project";
}
