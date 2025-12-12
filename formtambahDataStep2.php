<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB;

    $group_project = required_param('group_project', PARAM_INT);
    $cmid = required_param('cmid', PARAM_INT);

    $coursemodules_records = $DB->get_records('course_modules', ['id' => $cmid]);
    
    $coursemodule_record = reset($coursemodules_records); 

    // File handling
    if ($_FILES['step2_pondation']['error'] !== UPLOAD_ERR_OK) {
        print_error("Terjadi kesalahan saat mengunggah file: " . $_FILES['step2_pondation']['error']);
    }

    // File details
    $file_name1 = $_FILES['step2_pondation']['name'];
    $file_tmp1 = $_FILES['step2_pondation']['tmp_name'];

    if ($coursemodule_record) {
        $courseid = (int)$coursemodule_record->instance;
        $project_records = $DB->get_records('project', ['ebelajar' => $courseid]);  

        $count = $DB->count_records('project', ['ebelajar' => $courseid]);

        $file_base_name = ($count > 0 ? ($count + 1) . '_' : '') . $file_name1;
        $file_destination1 = 'jawaban_step2/' . $file_base_name;
        
        $unique_suffix = 1;
        while (file_exists($file_destination1)) {
            $file_info = pathinfo($file_base_name);
            $file_name_only = $file_info['filename'];
            $file_extension = isset($file_info['extension']) ? '.' . $file_info['extension'] : '';
            
            $file_destination1 = 'jawaban_step2/' . $file_name_only . '_' . $unique_suffix . $file_extension;
            $unique_suffix++;
        }
        
        if (!move_uploaded_file($file_tmp1, $file_destination1)) {
            print_error("Terjadi kesalahan saat mengunggah file.");
        }

        if ($project_records) {
            $project_record = reset($project_records);
            $record = new stdClass();
            $record->id = $project_record->id;
            $record->group_project = $group_project;
            $record->step2_pondation = $file_destination1;
            $record->status_step2 = "Selesai";
            $record->status_step3 = "Selesai";
            $record->updated_at = time();
            $record->status_step4 = "Mengerjakan";

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
