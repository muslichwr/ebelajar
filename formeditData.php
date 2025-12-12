<?php
require_once('../../config.php');

global $USER, $DB;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = required_param('id', PARAM_INT); 

    $nama_siswa = required_param('nama_siswa', PARAM_TEXT);
    $no_kelompok = required_param('no_kelompok', PARAM_INT);
    $jobdesk = required_param('jobdesk', PARAM_TEXT);
    
    $user_id = $USER->id;

    $record = $DB->get_record('groupstudentproject', ['id' => $id], '*', MUST_EXIST);
    if (!$record) {
        echo "Data tidak ditemukan!";
        exit;
    }

    $record->name_student = $nama_siswa;
    $record->groupproject = $no_kelompok;
    $record->jobdesk = $jobdesk;
    $record->updated_at = time();

    // Simpan perubahan
    $updated = $DB->update_record('groupstudentproject', $record);
    
    if ($updated) {
        echo "Data berhasil diubah";
    } else {
        echo "Error dalam mengubah data";
    }
} else {
    echo "Permintaan tidak valid!";
}
?>
