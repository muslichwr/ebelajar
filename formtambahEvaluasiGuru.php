<?php
/**
 * Backend Handler for Teacher Evaluation (Syntax 7)
 * Saves teacher feedback to evaluation_data JSON column
 * IMPORTANT: Merge-preserves student revision data
 */
require_once('../../config.php');
require_login();

$group_project = required_param('group_project', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT);
$teacher_feedback = required_param('teacher_feedback', PARAM_TEXT);

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

// Merge Logic: Read existing data first (preserves student revision!)
$data = [];
if (!empty($project->evaluation_data)) {
    $data = json_decode($project->evaluation_data, true);
    if (!is_array($data)) {
        $data = []; // Reset if corrupted
    }
}

// Update Teacher Feedback only
$data['teacher_feedback'] = $teacher_feedback;
$data['feedback_updated_at'] = date('c'); // ISO 8601 timestamp
$data['feedback_by'] = $USER->id;

$record = new stdClass();
$record->id = $project->id;
$record->evaluation_data = json_encode($data);
$record->status_step7 = 'Mengerjakan'; // Set to "Working" to signal student can submit revision
$record->updated_at = time();

if ($DB->update_record('project', $record)) {
    echo "Success";
} else {
    echo "Error: Failed to update project";
}
