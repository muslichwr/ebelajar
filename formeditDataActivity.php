<?php
require_once('../../config.php');
require_login();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    global $DB, $USER;

    // Ambil parameter dari POST
    $id = required_param('id', PARAM_INT);
    $group_project = required_param('group_project', PARAM_INT);
    $nama_kegiatan = required_param('nama_kegiatan', PARAM_TEXT);
    $uraian_kegiatan = required_param('uraian_kegiatan', PARAM_TEXT);
    $tanggal_kegiatan = required_param('tanggal_kegiatan', PARAM_TEXT);
    
    // File handling
    if ($_FILES['bukti_kegiatan']['error'] !== UPLOAD_ERR_OK) {
        die("Terjadi kesalahan saat mengunggah file: " . $_FILES['bukti_kegiatan']['error']);
    }

    // File destination
    $file_name = $_FILES['bukti_kegiatan']['name'];
    $file_tmp = $_FILES['bukti_kegiatan']['tmp_name'];
    $user_id = $USER->id;
    $student_name = fullname($USER);

    // Periksa apakah nama file sudah ada di database
    $count = $DB->count_records('activity_report', ['user_id' => $user_id]);

    if ($count > 0) {
        $file_destination = 'buktiKegiatanSiswa/' . $student_name . '_' . ($count + 1) . '_Edit_' . $file_name;
    } else {
        $file_destination = 'buktiKegiatanSiswa/' . $student_name . '_1_Edit_' . $file_name;
    }

    // Validasi ekstensi file
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    // Check if file extension is allowed
    if (!in_array($file_extension, $allowed_extensions)) {
        die("Hanya file gambar yang diizinkan.");
    }

    // Pindahkan file ke destinasi yang ditentukan
    if (!move_uploaded_file($file_tmp, $file_destination)) {
        die("Terjadi kesalahan saat mengunggah file.");
    }

    // Hapus file lama jika ada
    $old_file_path = required_param('old_file_path', PARAM_TEXT);
    if (file_exists($old_file_path)) {
        unlink($old_file_path);
    }

    $record = new stdClass();
    $record->id = $id;  
    $record->groupproject = $group_project; 
    $record->name_activity = $nama_kegiatan;
    $record->description_activity = $uraian_kegiatan;
    $record->date_activity = intval(strtotime($tanggal_kegiatan));
    $record->file_path = $file_destination;
    $record->updated_at = time(); 

    // Update record di database
    if ($DB->update_record('activity_report', $record)) {
        echo "Data berhasil diperbarui di tabel activity_report";
    } else {
        echo "Gagal memperbarui data.";
    }
    
} else {
    echo "Permintaan tidak valid!";
}
?>
