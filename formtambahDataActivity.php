<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB, $USER;

    $cmid = required_param('cmid', PARAM_INT);
    $group_project = required_param('group_project', PARAM_INT);
    $nama_kegiatan = required_param('nama_kegiatan', PARAM_TEXT);
    $uraian_kegiatan = required_param('uraian_kegiatan', PARAM_TEXT);
    $tanggal_kegiatan = required_param('tanggal_kegiatan', PARAM_TEXT);

    // File handling
    if ($_FILES['bukti_kegiatan']['error'] !== UPLOAD_ERR_OK) {
        print_error("Terjadi kesalahan saat mengunggah file: " . $_FILES['bukti_kegiatan']['error']);
    }

    // File details
    $file_name = $_FILES['bukti_kegiatan']['name'];
    $file_tmp = $_FILES['bukti_kegiatan']['tmp_name'];
    $user_id = $USER->id;
    $student_name = fullname($USER);

    $coursemodules_records = $DB->get_records('course_modules', ['id' => $cmid]);
    
    $coursemodule_record = reset($coursemodules_records);
    

    $count = $DB->count_records('activity_report', ['user_id' => $user_id]);

    if ($count > 0) {
        $file_destination = 'buktiKegiatanSiswa/' . $student_name . '_' . ($count + 1) . '_' . $file_name;
    } else {
        $file_destination = 'buktiKegiatanSiswa/' . $student_name . '_1_' . $file_name;
    }

    // Validasi ekstensi file
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
    if (!in_array($file_extension, $allowed_extensions)) {
        print_error("Hanya file gambar yang diizinkan.");
    }

    // Pindahkan file ke destinasi yang ditentukan
    if (!move_uploaded_file($file_tmp, $file_destination)) {
        print_error("Terjadi kesalahan saat mengunggah file.");
    }

    if ($coursemodule_record) {
        $courseid = intval($coursemodule_record->instance);

        $project_records = $DB->get_records('project', ['ebelajar' => $courseid, 'group_project' => $group_project]);
            
        if ($project_records) {
            $project_record = reset($project_records);
            
            if ($project_record) {
                $record = new stdClass();
                $record->ebelajar = intval($project_record->id);
                $record->user_id = intval($user_id);
                $record->groupproject = intval($group_project);
                $record->name_activity = $nama_kegiatan;
                $record->description_activity = $uraian_kegiatan;
                $record->date_activity = intval(strtotime($tanggal_kegiatan));
                $record->file_path = $file_destination;
                $record->created_at = $created_at;
                $record->updated_at = $updated_at;

                if ($DB->insert_record('activity_report', $record)) {
                    echo "Data berhasil ditambahkan ke tabel activity_report";
                } else {
                    print_error("Gagal menyimpan data.");
                }

            } else {
                echo "Record tidak ditemukan.";
            }      
        } else {
            echo "Tidak ada record project ditemukan.";
        }

        $projects = $DB->get_record('project', ['ebelajar' => $courseid, 'group_project' => $group_project]);
        if ($projects) {
            $project = new stdClass();
            $project->id = intval($projects->id);
            $project->ebelajar = intval($projects->ebelajar);
            $project->status_step4 = "Selesai";
            $project->status_step5 = "Mengerjakan";
        
            if ($DB->update_record('project', $project)) {
                echo "Data berhasil diperbarui di tabel project";
            } else {
                print_error("Gagal menyimpan data.");
            }
        } else {
            print_error("Record project tidak ditemukan.");
        }
    } else {
        echo "Record course module tidak ditemukan.";
    }


} else {
    print_error("Permintaan tidak valid!");
}

?>
