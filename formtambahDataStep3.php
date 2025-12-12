<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB;

    $cmid = required_param('id', PARAM_INT);

    // File handling
    if ($_FILES['file_step3']['error'] !== UPLOAD_ERR_OK) {
        print_error("Terjadi kesalahan saat mengunggah file: " . $_FILES['file_step3']['error']);
    }

    // File details
    $file_name1 = $_FILES['file_step3']['name'];
    $file_tmp1 = $_FILES['file_step3']['tmp_name'];

    $coursemodules_records = $DB->get_records('course_modules', ['id' => $cmid]);
    
    $coursemodule_record = reset($coursemodules_records); 

    $count = $DB->count_records('ebelajar', ['coursemoduleid' => $cmid]);

    if ($count > 0) {
        $file_destination1 = 'jadwal_file/' . ($count + 1) . '_' . $file_name1;
    } else {
        $file_destination1 = 'jadwal_file/' . $file_name1;
    }

    // Validasi ekstensi file
    $file_extension1 = strtolower(pathinfo($file_name1, PATHINFO_EXTENSION));

    // Pindahkan file ke destinasi yang ditentukan
    if (!move_uploaded_file($file_tmp1, $file_destination1)) {
        print_error("Terjadi kesalahan saat mengunggah file.");
    }

    if ($_FILES['file_image']['error'] !== UPLOAD_ERR_OK) {
        print_error("Terjadi kesalahan saat mengunggah file: " . $_FILES['file_image']['error']);
    }

    // File details
    $file_name2 = $_FILES['file_image']['name'];
    $file_tmp2 = $_FILES['file_image']['tmp_name'];

    if ($count > 0) {
        $file_destination2 = 'jadwal_image/' . ($count + 1) . '_' . $file_name2;
    } else {
        $file_destination2 = 'jadwal_image/' . $file_name2;
    }

    // Validasi ekstensi file
    $file_extension2 = strtolower(pathinfo($file_name2, PATHINFO_EXTENSION));

    // Pindahkan file ke destinasi yang ditentukan
    if (!move_uploaded_file($file_tmp2, $file_destination2)) {
        print_error("Terjadi kesalahan saat mengunggah file.");
    }

    $created_at = time();
    $updated_at = $created_at;

    if ($coursemodule_record) {
        $courseid = (int)$coursemodule_record->instance;
        $ebelajar_records = $DB->get_records('ebelajar', ['id' => $courseid]);  
        
        if ($ebelajar_records) {
            $ebelajar_record = reset($ebelajar_records);
            $record = new stdClass();
            $record->id = $ebelajar_record->id;
            $record->step3_schedule_image = $file_destination2;
            $record->step3_schedule_file = $file_destination1;
            $record->updated_at = $updated_at;

            if ($DB->update_record('ebelajar', $record)) {
                echo "Data berhasil ditambahkan ke tabel ebelajar";
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
