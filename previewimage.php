<?php

require_once(dirname(__FILE__).'/../../../../config.php');
global $CFG, $DB, $PAGE;
require_once($CFG->libdir.'/filelib.php');

$contextid = required_param('context', PARAM_INT);

$context = context::instance_by_id($contextid);
if ($context->contextlevel != CONTEXT_COURSE) {
    send_file_not_found();
}

$PAGE->set_url('/');
$PAGE->set_context($context);
require_login();
require_capability('moodle/course:manageactivities', $context);

$fs = get_file_storage();
$file = $fs->get_file($context->id, 'assignsubmission_pdf', 'previewimage', 0, '/', 'preview.png');

if ($file) {
    send_stored_file($file);
}

send_file_not_found();