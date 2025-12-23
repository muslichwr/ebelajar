<?php
/**
 * Backend Handler for Syntax 8 (Refleksi Pembelajaran)
 * Saves student reflection data to reflection_data JSON column
 */
require_once('../../config.php');
require_login();

$group_project = required_param('group_project', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$reflection_data = required_param('reflection_data', PARAM_RAW);

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

// Validate JSON
$decoded = json_decode($reflection_data, true);
if (!is_array($decoded)) {
    die('Error: Invalid reflection data format');
}

// Add metadata
$decoded['submitted_at'] = date('c');
$decoded['submitted_by'] = $USER->id;

$record = new stdClass();
$record->id = $project->id;
$record->reflection_data = json_encode($decoded);
$record->status_step8 = 'Selesai';
$record->updated_at = time();

if ($DB->update_record('project', $record)) {
    echo "Success";
} else {
    echo "Error: Failed to save reflection";
}
