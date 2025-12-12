<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB, $USER;

    $group_project = required_param('group_project', PARAM_INT);
    $cmid = required_param('cmid', PARAM_INT);
    $title_project = required_param('title_project', PARAM_TEXT);
    $description_project = optional_param('description_project', null, PARAM_TEXT);

    if ($_FILES['file_project']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['file_project']['name'];
        $file_tmp = $_FILES['file_project']['tmp_name'];
        
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $base_name = pathinfo($file_name, PATHINFO_FILENAME);
        
        $directory = 'project_files/';
        
        $file_destination = $directory . $file_name;
        $counter = 1;

        while (file_exists($file_destination)) {
            $file_destination = $directory . $base_name . "($counter)." . $file_extension;
            $counter++;
        }
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'zip', 'rar'];

        if (!in_array($file_extension, $allowed_extensions)) {
            print_error("Hanya file gambar, dokumen, dan arsip yang diizinkan.");
        }

        if (!move_uploaded_file($file_tmp, $file_destination)) {
            print_error("Terjadi kesalahan saat mengunggah file.");
        }
    } else {
        $file_destination = null;
    }


    $coursemodules_records = $DB->get_records('course_modules', ['id' => $cmid]);   
    $coursemodule_record = reset($coursemodules_records);

    if ($coursemodule_record) {
        $courseid = intval($coursemodule_record->instance);
        $ebelajar_records = $DB->get_records('ebelajar', ['id' => $courseid]);
    
        if ($ebelajar_records) {
            $ebelajar_record = reset($ebelajar_records);

            $projects = $DB->get_record('project', ['ebelajar' => $courseid, 'group_project' => $group_project]);
            
            if ($ebelajar_record) {
                if ($file_destination != null) {
                    $updated_at = time();
                
                    $project_record = new stdClass();
                    $project_record->id = intval($projects->id);
                    $project_record->title_project = $title_project;
                    $project_record->description_project = $description_project;
                    $project_record->file_path = $file_destination;
                    $project_record->updated_at = $updated_at;
                    $project_record->status_step5 = "Selesai";
                    $project_record->status_step6 = "Mengerjakan";
                
                    // Simpan ke tabel project
                    if ($DB->update_record('project', $project_record)) {
                        echo "Data project berhasil ditambahkan!";
                    } else {
                        print_error("Gagal menambahkan data project.");
                    }

                } else {
                    $updated_at = time();

                    $project_record = new stdClass();
                    $project_record->id = intval($projects->id);
                    $project_record->group_project = intval($group_project);
                    $project_record->ebelajar = intval($ebelajar_record->id);
                    $project_record->title_project = $title_project;
                    $project_record->description_project = $description_project;
                    $project_record->updated_at = $updated_at;
                    $project_record->status_step5 = "Selesai";
                    $project_record->status_step6 = "Mengerjakan";
                
                    // Simpan ke tabel project
                    if ($DB->update_record('project', $project_record)) {
                        echo "Data project berhasil ditambahkan!";
                    } else {
                        print_error("Gagal menambahkan data project.");
                    }
                }
                
            } else {
                echo "Record tidak ditemukan.";
            }      
        } else {
            echo "Tidak ada record ebelajar ditemukan.";
        }
    } else {
        echo "Record course module tidak ditemukan.";
    }

} else {
    print_error("Permintaan tidak valid.");
}
?>
