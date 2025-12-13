<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB;
    
    $group_project_id = required_param('group_project_id', PARAM_INT);
    $leader_user_id = required_param('leader_user_id', PARAM_INT);
    $teacher_scenario = optional_param('teacher_scenario', '', PARAM_TEXT);
    $cmid = required_param('cmid', PARAM_INT);
    
    // Get ebelajar instance ID from course module
    $ebelajar_id = $DB->get_field('ebelajar', 'id', ['coursemoduleid' => $cmid]);
    
    if (!$ebelajar_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid course module']);
        exit;
    }
    
    // Verify leader is part of this group
    $member = $DB->get_record('groupstudentproject', [
        'groupproject' => $group_project_id,
        'user_id' => $leader_user_id
    ]);
    
    if (!$member) {
        echo json_encode(['success' => false, 'message' => 'Siswa bukan anggota kelompok ini']);
        exit;
    }
    
    // TODO: Add Top 10 ranking validation here when gradebook integration is ready
    // For now, we'll allow any student to be leader
    
    try {
        // Reset all members to non-leader first
        $DB->execute("UPDATE {groupstudentproject} SET is_leader = 0 WHERE groupproject = ?", [$group_project_id]);
        
        // Set new leader
        $DB->set_field('groupstudentproject', 'is_leader', 1, ['id' => $member->id]);
        
        // Update teacher scenario if provided
        if (!empty($teacher_scenario)) {
            $DB->set_field('ebelajar', 'teacher_scenario', $teacher_scenario, ['id' => $ebelajar_id]);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Ketua kelompok berhasil ditetapkan',
            'leader_name' => $member->name_student
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan data: ' . $e->getMessage()]);
    }
    
} else {
    print_error("Permintaan tidak valid!");
}
?>
