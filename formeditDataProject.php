<?php
require_once('../../config.php');
require_login();

$project_id = required_param('id', PARAM_INT);

$project_data = $DB->get_record('project', ['id' => $project_id]);

if (!$project_data) {
    print_error("Data proyek tidak ditemukan.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari formulir
    $group_project = required_param('group_project', PARAM_INT);
    $title_project = required_param('title_project', PARAM_TEXT);
    $description_project = optional_param('description_project', null, PARAM_TEXT);

    $file_destination = null;

    if ($_FILES['file_project']['error'] === UPLOAD_ERR_OK) {
        // Detail file
        $file_name = $_FILES['file_project']['name'];
        $file_tmp = $_FILES['file_project']['tmp_name'];
        
        $directory = 'project_files/';
        
        $file_destination = $directory . $file_name;
        $counter = 1;

        while (file_exists($file_destination)) {
            $file_destination = $directory . pathinfo($file_name, PATHINFO_FILENAME) . "($counter)." . pathinfo($file_name, PATHINFO_EXTENSION);
            $counter++;
        }

        if (!move_uploaded_file($file_tmp, $file_destination)) {
            print_error("Terjadi kesalahan saat mengunggah file.");
        }

        if (!empty($project_data->file_path) && file_exists($project_data->file_path)) {
            unlink($project_data->file_path);
        }
    }

    $project_record = new stdClass();
    $project_record->id = intval($project_id); 
    $project_record->group_project = intval($group_project);
    $project_record->title_project = $title_project;
    $project_record->description_project = $description_project;
    
    if ($file_destination) {
        $project_record->file_path = $file_destination;
    } else {
        $project_record->file_path = $project_data->file_path;
    }

    if ($DB->update_record('project', $project_record)) {
        echo "Data proyek berhasil diperbarui!";
    } else {
        print_error("Gagal memperbarui data proyek.");
    }
}
?>
