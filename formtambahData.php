<?php
require_once('../../config.php');

global $USER, $DB;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_siswa = required_param('nama_siswa', PARAM_TEXT);
    $no_kelompok = required_param('no_kelompok', PARAM_INT);
    $jobdesk = required_param('jobdesk', PARAM_TEXT);
    
    $user_id = $USER->id;

    $cmid = required_param('id', PARAM_INT);
    
    $created_at = time();
    $updated_at = time();

    $coursemodules_records = $DB->get_records('course_modules', ['id' => $cmid]);
    
    $coursemodule_record = reset($coursemodules_records); 

    $groupproject_record = $DB->get_record('groupproject', ['id' => $no_kelompok, 'ebelajar' => (int)$coursemodule_record->instance], '*', IGNORE_MULTIPLE);

    if (!$groupproject_record) {
        echo "Kelompok dengan nomor $no_kelompok tidak ditemukan!";
        exit;
    }
    $groupproject_id = $groupproject_record->id;

    $ebelajar_record = $DB->get_record('ebelajar', [], '*', IGNORE_MISSING);
    if (!$ebelajar_record) {
        echo "Data ebelajar tidak ditemukan!";
        exit;
    }
    $ebelajar_id = (int)$coursemodule_record->instance;

    $data = new stdClass();
    $data->user_id = $user_id;
    $data->groupproject = intval($groupproject_id);
    $data->ebelajar = intval($ebelajar_id); 
    $data->name_student = $nama_siswa;
    $data->jobdesk = $jobdesk;
    $data->created_at = $created_at;
    $data->updated_at = $updated_at;

    $inserted = $DB->insert_record('groupstudentproject', $data);
    
    if ($inserted) {
        echo "Data berhasil ditambahkan ke tabel project";
    } else {
        echo "Error dalam menambahkan data ke tabel project";
    }
} else {
    echo "Permintaan tidak valid!";
}
?>
