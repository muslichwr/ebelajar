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

    // Mengecek data di tabel 'ebelajar'
    $ebelajar_records = $DB->get_records('ebelajar', ['coursemoduleid' => $cmid]);
    $ebelajar_record = reset($ebelajar_records);

    // Menghapus file lama jika ada
    if ($ebelajar_record) {
        if (file_exists($ebelajar_record->step3_schedule_file)) {
            unlink($ebelajar_record->step3_schedule_file);
        }
        if (file_exists($ebelajar_record->step3_schedule_image)) {
            unlink($ebelajar_record->step3_schedule_image);
        }
    }

    // Menentukan jalur file baru
    $file_destination1 = 'jadwal_file/' . $file_name1;
    $file_extension1 = strtolower(pathinfo($file_name1, PATHINFO_EXTENSION));

    if (!move_uploaded_file($file_tmp1, $file_destination1)) {
        print_error("Terjadi kesalahan saat mengunggah file.");
    }

    if ($_FILES['file_image']['error'] !== UPLOAD_ERR_OK) {
        print_error("Terjadi kesalahan saat mengunggah file: " . $_FILES['file_image']['error']);
    }

    // File details
    $file_name2 = $_FILES['file_image']['name'];
    $file_tmp2 = $_FILES['file_image']['tmp_name'];
    $file_destination2 = 'jadwal_image/' . $file_name2;
    $file_extension2 = strtolower(pathinfo($file_name2, PATHINFO_EXTENSION));

    if (!move_uploaded_file($file_tmp2, $file_destination2)) {
        print_error("Terjadi kesalahan saat mengunggah file.");
    }

    $updated_at = time();

    // Update record di database
    $record = new stdClass();
    $record->id = $ebelajar_record->id;
    $record->step3_schedule_image = $file_destination2;
    $record->step3_schedule_file = $file_destination1;
    $record->updated_at = $updated_at;

    if ($DB->update_record('ebelajar', $record)) {
        echo "Data berhasil diperbarui di tabel ebelajar";
    } else {
        echo "Gagal memperbarui data";  
    }
} else {
    print_error("Permintaan tidak valid!");
}
?>
