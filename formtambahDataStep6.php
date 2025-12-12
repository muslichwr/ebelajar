<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB;

    $group_project = required_param('group_project', PARAM_INT);
    $cmid = required_param('cmid', PARAM_INT);
    $evaluation = required_param('evaluation', PARAM_TEXT);

    $coursemodules_records = $DB->get_records('course_modules', ['id' => $cmid]);
    
    $coursemodule_record = reset($coursemodules_records); 

    $updated_at = time();

    if ($coursemodule_record) {
        $courseid = (int)$coursemodule_record->instance;
        $project_records = $DB->get_records('project', ['ebelajar' => $courseid]);  
        
        if ($project_records) {
            $project_record = reset($project_records);
            $record = new stdClass();
            $record->id = $project_record->id;
            $record->group_project = $group_project;
            $record->evaluation = $evaluation;
            $record->status_step6 = "Selesai";
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
