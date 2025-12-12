<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB;

    $group_project = required_param('group_project', PARAM_INT);
    $cmid = required_param('cmid', PARAM_INT);

    $file_destination = null;

    $project_records = $DB->get_records('project', ['group_project' => $group_project]);
    $project_record = reset($project_records);

    if ($_FILES['step2_pondation']['error'] === UPLOAD_ERR_OK) {
        // Detail file
        $file_name = $_FILES['step2_pondation']['name'];
        $file_tmp = $_FILES['step2_pondation']['tmp_name'];
        
        $directory = 'jawaban_step2/';
        
        $file_destination = $directory . $file_name;
        $counter = 1;

        while (file_exists($file_destination)) {
            $file_destination = $directory . pathinfo($file_name, PATHINFO_FILENAME) . "($counter)." . pathinfo($file_name, PATHINFO_EXTENSION);
            $counter++;
        }

        if (!move_uploaded_file($file_tmp, $file_destination)) {
            print_error("Terjadi kesalahan saat mengunggah file.");
        }

        if (!empty($project_record->step2_pondation) && file_exists($project_record->step2_pondation)) {
            unlink($project_record->step2_pondation);
        }
    }

    $updated_at = time();

    $project = new stdClass();
    $project->id = intval($project_record->id); 
    $project->group_project = intval($group_project);
    
    if ($file_destination) {
        $project->step2_pondation = $file_destination;
    } else {
        $project->step2_pondation = $project_record->step2_pondation;
    }

    if ($DB->update_record('project', $project)) {
        echo "Data proyek berhasil diperbarui!";
    } else {
        print_error("Gagal memperbarui data proyek.");
    }

} else {
    print_error("Permintaan tidak valid!");
}
?>
