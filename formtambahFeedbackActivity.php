<?php

require_once('../../config.php');

require_login(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = required_param('id', PARAM_INT);
    $feedback = required_param('feedback', PARAM_TEXT);

    global $DB;

    $record = new stdClass();
    $record->id = intval($id);
    $record->feedback_teacher = $feedback;

    try {
        $DB->update_record('activity_report', $record);
        echo "Data berhasil diupdate ke tabel activity_report";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Permintaan tidak valid!";
}
?>
