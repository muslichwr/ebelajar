<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB, $USER;

    $group_project = required_param('group_project', PARAM_INT);
    $cmid = required_param('cmid', PARAM_INT);
    $step1_formulation = required_param('step1_formulation', PARAM_TEXT);
    $indicators_json = optional_param('indicators', '', PARAM_RAW);
    
    // Verify user is the group leader
    $member = $DB->get_record('groupstudentproject', [
        'groupproject' => $group_project,
        'user_id' => $USER->id
    ]);
    
    if (!$member || !$member->is_leader) {
        echo json_encode(['success' => false, 'message' => 'Hanya ketua kelompok yang dapat mengubah data']);
        exit;
    }

    // Get project record by group_project ID (NOT by ebelajar instance)
    $project_record = $DB->get_record('project', ['group_project' => $group_project]);
    
    if (!$project_record) {
        echo json_encode(['success' => false, 'message' => 'Project untuk kelompok ini tidak ditemukan']);
        exit;
    }

    $updated_at = time();

    try {
        // Start transaction
        $transaction = $DB->start_delegated_transaction();
        
        // Update project record
        $record = new stdClass();
        $record->id = $project_record->id;
        $record->group_project = $group_project;
        $record->step1_formulation = $step1_formulation;
        $record->updated_at = $updated_at;

        $DB->update_record('project', $record);
        
        // Delete old indicators and insert new ones
        if (!empty($indicators_json)) {
            // Delete existing indicators for this project
            $DB->delete_records('project_indicators', ['project_id' => $project_record->id]);
            
            $indicators = json_decode($indicators_json, true);
            
            if (is_array($indicators)) {
                foreach ($indicators as $indicator) {
                    // Validate minimum 3 references
                    $references = json_decode($indicator['references'], true);
                    if (!is_array($references) || count($references) < 3) {
                        throw new Exception('Setiap indikator harus memiliki minimal 3 referensi');
                    }
                    
                    $ind_record = new stdClass();
                    $ind_record->project_id = $project_record->id;
                    $ind_record->indicator_name = $indicator['name'];
                    $ind_record->analysis = $indicator['analysis'];
                    $ind_record->references = $indicator['references'];
                    $ind_record->is_valid = isset($indicator['is_valid']) ? (int)$indicator['is_valid'] : 1;
                    $ind_record->created_at = time();
                    $ind_record->updated_at = time();
                    
                    $DB->insert_record('project_indicators', $ind_record);
                }
            }
        }
        
        // Commit transaction
        $transaction->allow_commit();
        
        echo json_encode(['success' => true, 'message' => 'Data berhasil diperbarui']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui: ' . $e->getMessage()]);
    }
    
} else {
    print_error("Permintaan tidak valid!");
}
?>
