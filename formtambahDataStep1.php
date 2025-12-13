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
        echo json_encode(['success' => false, 'message' => 'Hanya ketua kelompok yang dapat menyimpan data']);
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
        
        // Update project table
        $record = new stdClass();
        $record->id = $project_record->id;
        $record->group_project = $group_project;
        $record->step1_formulation = $step1_formulation;
        $record->status_step1 = "Selesai";
        $record->status_step2 = "Mengerjakan";
        $record->updated_at = $updated_at;
        
        if (!$DB->update_record('project', $record)) {
            throw new Exception('Gagal update tabel project');
        }
        
        // Process indicators if provided
        if (!empty($indicators_json)) {
            $indicators = json_decode($indicators_json, true);
            
            if (!is_array($indicators)) {
                throw new Exception('Format data indikator tidak valid');
            }
            
            // Check if project_indicators table exists
            $dbman = $DB->get_manager();
            $table = new xmldb_table('project_indicators');
            if (!$dbman->table_exists($table)) {
                throw new Exception('Tabel project_indicators belum ada. Silakan upgrade database terlebih dahulu di Admin > Notifications');
            }
            
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
                $ind_record->references = $indicator['references']; // Already JSON string
                $ind_record->is_valid = isset($indicator['is_valid']) ? (int)$indicator['is_valid'] : 1;
                $ind_record->created_at = time();
                $ind_record->updated_at = time();
                
                if (!$DB->insert_record('project_indicators', $ind_record)) {
                    throw new Exception('Gagal menyimpan indikator: ' . $indicator['name']);
                }
            }
        }
        
        // Commit transaction
        $transaction->allow_commit();
        
        echo json_encode(['success' => true, 'message' => 'Data berhasil disimpan']);
        
    } catch (Exception $e) {
        // Rollback on error
        if (isset($transaction)) {
            try {
                $transaction->rollback($e);
            } catch (Exception $rollback_error) {
                // Ignore rollback errors
            }
        }
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
} else {
    print_error("Permintaan tidak valid!");
}
?>
