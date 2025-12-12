<?php

require_once('../../config.php');

global $DB;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];

        $record = $DB->get_record('activity_report', ['id' => $id], 'file_path');

        if ($record) {
            $file_path = $record->file_path;

            if ($DB->delete_records('activity_report', ['id' => $id])) {
                if (unlink($file_path)) {
                    echo "Data kegiatan dan file terkait berhasil dihapus.";
                } else {
                    echo "Data kegiatan berhasil dihapus, tetapi terjadi kesalahan saat menghapus file.";
                }
            } else {
                echo "Terjadi kesalahan saat menghapus data kegiatan.";
            }
        } else {
            echo "Data kegiatan tidak ditemukan.";
        }
    } else {
        echo "ID kegiatan tidak ditemukan.";
    }
} else {
    echo "Permintaan tidak valid!";
}


?>
