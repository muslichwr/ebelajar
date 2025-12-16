<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB;

    $group_project = required_param('group_project', PARAM_INT);
    $cmid = required_param('cmid', PARAM_INT);
    // Accept JSON data directly as raw text - contains full array with appended entry
    $logbook_data = optional_param('logbook_data', '[]', PARAM_RAW);

    $coursemodules_records = $DB->get_records('course_modules', ['id' => $cmid]);
    
    $coursemodule_record = reset($coursemodules_records); 

    if ($coursemodule_record) {
        $courseid = (int)$coursemodule_record->instance;
        
        // CRITICAL: Filter by BOTH ebelajar AND group_project for data isolation
        $project_records = $DB->get_records('project', [
            'ebelajar' => $courseid,
            'group_project' => $group_project
        ]);

        if ($project_records) {
            $project_record = reset($project_records);
            $record = new stdClass();
            $record->id = $project_record->id;
            $record->group_project = $group_project;
            $record->logbook_data = $logbook_data;
            $record->status_step3 = "Selesai";
            $record->status_step4 = "Selesai";
            $record->status_step5 = "Mengerjakan";
            $record->updated_at = time();

            if ($DB->update_record('project', $record)) {
                echo "Logbook berhasil disimpan";
            } else {
                echo "Gagal menyimpan logbook";  
            }
        } else {
            echo "Tidak ada record project ditemukan untuk kelompok ini.";
        }
    } else {
        echo "Record course module tidak ditemukan.";
    }
} else {
    print_error("Permintaan tidak valid!");
}

?>
