<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB;

    $group_project = required_param('group_project', PARAM_INT);
    $cmid = required_param('cmid', PARAM_INT);
    $step1_formulation = required_param('step1_formulation', PARAM_TEXT);
    $problem_definition = optional_param('problem_definition', '', PARAM_TEXT);
    $analysis_data = optional_param('analysis_data', '', PARAM_TEXT);

    $coursemodules_records = $DB->get_records('course_modules', ['id' => $cmid]);
    
    $coursemodule_record = reset($coursemodules_records); 

    $updated_at = $created_at;

    if ($coursemodule_record) {
        $courseid = (int)$coursemodule_record->instance;
        // CRITICAL FIX: Filter by BOTH ebelajar AND group_project to prevent data leak
        $project_records = $DB->get_records('project', [
            'ebelajar' => $courseid,
            'group_project' => $group_project
        ]);  
        
        if ($project_records) {
            $project_record = reset($project_records);
            $record = new stdClass();
            $record->id = $project_record->id;
            $record->group_project = $group_project;
            $record->step1_formulation = $step1_formulation;
            $record->problem_definition = $problem_definition;
            $record->analysis_data = $analysis_data;
            $record->status_step1 = "Selesai";
            $record->status_step2 = "Mengerjakan";
            $record->updated_at = $updated_at;

            if ($DB->update_record('project', $record)) {
                echo "Data berhasil ditambahkan ke tabel project";
            } else {
                echo "Gagal menambahkan data";  
            }
        } else {
            echo "Tidak ada record ebelajar ditemukan.";
        }
    } else {
        echo "Record course module tidak ditemukan.";
    }
} else {
    print_error("Permintaan tidak valid!");
}

?>
