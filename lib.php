<?php

defined('MOODLE_INTERNAL') || die();

/**
 * This function is called when a course module is viewed.
 * It logs the view event for the C++ Compiler plugin.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 */

function ebelajar_get_coursemodule_info($cm) {
    global $DB;

    $info = new stdClass();
    $instance = $DB->get_record('ebelajar', ['id' => $cm->instance], 'id, name', MUST_EXIST);
    $info->name = $instance->name;
    return $info;
}

function ebelajar_add_instance($data) {
    global $DB;

    $record = new stdClass();
    $record->course = (int)$data->course;
    $record->coursemoduleid = $data->coursemodule;
    $record->name = trim($data->name);
    $record->intro = $data->desc;
    $record->case_study = $data->case_study;
    $record->total_grup = (int)$data->total_grup;
    $record->timemodified = time();
    
    // Menyimpan record ebelajar
    $id = $DB->insert_record('ebelajar', $record);
    if (!$id) {
        throw new Exception('Error inserting record');
    }

    $group_project_ids = [];
    $project_ids = [];

    for ($i = 1; $i <= $record->total_grup; $i++) {
        $group_project = new stdClass();
        $group_project->ebelajar = (int)$id;
        $group_project->group_number = (string)$i;

        $group_project_id = $DB->insert_record('groupproject', $group_project);
        if (!$group_project_id) {
            throw new Exception('Error inserting group project record');
        }
        $group_project_ids[] = $group_project_id; 

        $project = new stdClass();
        $project->ebelajar = (int)$id;
        $project->group_project = (int)$group_project_id;
        $project->status_step1 = 'Belum Selesai';
        $project->status_step2 = 'Belum Selesai';
        $project->status_step3 = 'Belum Selesai';
        $project->status_step4 = 'Belum Selesai';
        $project->status_step5 = 'Belum Selesai';
        $project->status_step6 = 'Belum Selesai';
        $project->created_at = time();
        $project->updated_at = time();

        $project_id = $DB->insert_record('project', $project);
        if (!$project_id) {
            throw new Exception('Error inserting project record');
        }
        $project_ids[] = $project_id; 
    }

    return $id; // Mengembalikan ID ebelajar
}



function ebelajar_update_instance($data) {
    global $DB;

    $record = $DB->get_record('ebelajar', array('id' => $data->instance), '*', MUST_EXIST);
    $old_total_grup = $record->total_grup;

    $record->name = trim($data->name);
    $record->intro = $data->desc;
    $record->case_study = $data->case_study;
    $record->total_grup = (int)$data->total_grup;
    $record->timemodified = time();

    $DB->update_record('ebelajar', $record);

    $new_total_grup = $record->total_grup;

    if ($new_total_grup == $old_total_grup) {
        return true;
    }

    $existing_groups = $DB->get_records('groupproject', array('ebelajar' => $record->id));

    if ($new_total_grup > $old_total_grup) {
        for ($i = $old_total_grup + 1; $i <= $new_total_grup; $i++) {
            $group_project = new stdClass();
            $group_project->ebelajar = (int)$record->id;
            $group_project->group_number = (string)$i;

            $group_project_id = $DB->insert_record('groupproject', $group_project);
            if (!$group_project_id) {
                throw new Exception('Error inserting new group project record');
            }

            $project = new stdClass();
            $project->ebelajar = (int)$record->id;
            $project->group_project = (int)$group_project_id;
            $project->status_step1 = 'Belum Selesai';
            $project->status_step2 = 'Belum Selesai';
            $project->status_step3 = 'Belum Selesai';
            $project->status_step4 = 'Belum Selesai';
            $project->status_step5 = 'Belum Selesai';
            $project->status_step6 = 'Belum Selesai';
            $project->created_at = time();
            $project->updated_at = time();

            $project_id = $DB->insert_record('project', $project);
            if (!$project_id) {
                throw new Exception('Error inserting project record for new group');
            }
        }
    } 
    else {
        for ($i = $new_total_grup + 1; $i <= $old_total_grup; $i++) {
            if (isset($existing_groups[$i])) {
                $group_project_id = $existing_groups[$i]->id;
                $projects_to_delete = $DB->get_records('project', array('group_project' => $group_project_id));
                foreach ($projects_to_delete as $project) {
                    $DB->delete_records('project', array('id' => $project->id));
                }

                $DB->delete_records('groupproject', array('id' => $group_project_id));
            }
        }
    }

    return true; // Pembaruan berhasil
}

function ebelajar_delete_instance($id) {
    global $DB;

    return $DB->delete_records('ebelajar', ['id' => $id]);
}

/**
 * Serves files for the mod_ebelajar plugin.
 * This is the "Gatekeeper" function that Moodle calls when accessing pluginfile.php URLs.
 *
 * @param stdClass $course The course object
 * @param stdClass $cm The course module object
 * @param stdClass $context The context object
 * @param string $filearea The file area
 * @param array $args Extra arguments (itemid, filepath, filename)
 * @param bool $forcedownload Whether to force download
 * @param array $options Additional options
 * @return bool|void False if file not found, or serves the file
 */
function ebelajar_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB;

    // Security: Require login for this course and course module
    require_login($course, true, $cm);

    // Allowed file areas for this plugin
    $allowed_areas = ['product_evidence', 'presentation_file'];
    
    // Only serve files from allowed file areas
    if (!in_array($filearea, $allowed_areas)) {
        return false;
    }

    // Extract the item ID (project ID) from the arguments
    $itemid = array_shift($args);

    // Reconstruct the file path
    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    // Access the file storage
    $fs = get_file_storage();

    // Get the file using the "Triangle of Consistency" identifiers
    $file = $fs->get_file(
        $context->id,           // Context ID
        'mod_ebelajar',         // Component
        $filearea,              // File area (dynamic: product_evidence or presentation_file)
        $itemid,                // Item ID (project ID)
        $filepath,              // File path
        $filename               // File name
    );

    // If the file doesn't exist or is a directory, return false
    if (!$file || $file->is_directory()) {
        return false;
    }

    // Serve the file with caching (1 day = 86400 seconds)
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}