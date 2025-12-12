<?php

require_once('../../config.php');
global $DB, $USER;

$user_id = $USER->id;

$role_assignment = $DB->get_record('role_assignments', array('userid' => $user_id), 'roleid', IGNORE_MULTIPLE);

if ($role_assignment) {
    $roleid = $role_assignment->roleid;

    if ($roleid >= 1 && $roleid <= 4) {
        include 'logaktivitasKelompok.php';
    } elseif ($roleid == 5) {
        include 'logaktivitasSiswa.php';
    } else {
        echo 'Role tidak dikenali!';
    }
} else {
    echo 'Tidak ada data untuk roleid';
}


?>