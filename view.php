<?php

require_once('../../config.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('ebelajar', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
$PAGE->set_context($context);

$PAGE->set_url('/mod/ebelajar/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($cm->name));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
require_once('logaktivitas.php');
echo $OUTPUT->footer();
