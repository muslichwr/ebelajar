<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB;

    $group_project = required_param('group_project', PARAM_INT);
    $cmid = required_param('cmid', PARAM_INT);
    $step1_formulation = required_param('step1_formulation', PARAM_TEXT);

    // Mengecek data di tabel 'ebelajar'
    $project_records = $DB->get_records('project', ['group_project' => $group_project]);
    $project_record = reset($project_records);

    $updated_at = time();

    // Update record di database
    $record = new stdClass();
    $record->id = $project_record->id;
    $record->groupproject = $group_project;
    $record->step1_formulation = $step1_formulation;
    $record->updated_at = $updated_at;

    if ($DB->update_record('project', $record)) {
        echo "Data berhasil ditambahkan ke tabel project";
    } else {
        echo "Gagal menambahkan data";  
    }
} else {
    print_error("Permintaan tidak valid!");
}
?>
