<?php

require_once(dirname(__FILE__).'/../../../../config.php');
global $CFG, $DB, $PAGE;
require_once($CFG->dirroot.'/mod/assign/submission/pdf/edittemplates_class.php');

$courseid   = required_param('courseid', PARAM_INT);          // Course ID
$templateid = optional_param('templateid', 0, PARAM_INT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$imagetime = optional_param('imagetime', false, PARAM_INT); // Time when the preview image was uploaded

$course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);

$url = new moodle_url('/mod/assign/submission/pdf/edittemplates.php', array('courseid' => $courseid));
if ($templateid) {
    $url->param('templateid', $templateid);
}
if ($itemid) {
    $url->param('itemid', $itemid);
}
if ($imagetime) {
    $url->param('imagetime', $imagetime);
}
$PAGE->set_url($url);

require_login($course->id, false);

$context = context_course::instance($course->id);
require_capability('moodle/course:manageactivities', $context);

$edittmpl = new edit_templates($course->id, $templateid, $imagetime, $itemid);
$edittmpl->view();
