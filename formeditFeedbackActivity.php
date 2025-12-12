<?php
require_once('../../config.php');

require_login(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedback = required_param('feedback', PARAM_TEXT);
    $id = required_param('id', PARAM_INT);

    $record = new stdClass();
    $record->id = intval($id);
    $record->feedback_teacher = $feedback; 

    try {
        $DB->update_record('activity_report', $record); 
        echo "Data berhasil diperbarui di tabel activity_report";
    } catch (dml_exception $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Permintaan tidak valid!";
}
?>
