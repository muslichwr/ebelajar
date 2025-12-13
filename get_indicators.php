<?php
require_once('../../config.php');
require_login();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    global $DB;
    
    $group_project = required_param('group_project', PARAM_INT);
    
    // Get project ID
    $project = $DB->get_record('project', ['group_project' => $group_project], 'id');
    
    if (!$project) {
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }
    
    // Get indicators
    $indicators = $DB->get_records('project_indicators', ['project_id' => $project->id], 'created_at ASC');
    
    $result = [];
    foreach ($indicators as $ind) {
        $result[] = [
            'id' => $ind->id,
            'indicator_name' => $ind->indicator_name,
            'analysis' => $ind->analysis,
            'references' => $ind->references,
            'is_valid' => $ind->is_valid
        ];
    }
    
    echo json_encode(['success' => true, 'indicators' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
