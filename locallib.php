<?php
// This file is part of the submit PDF plugin for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the definition for the library class for pdf
 *  submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package   assignsubmission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/mod/assign/submission/pdf/lib.php');
require_once($CFG->dirroot.'/mod/assign/submission/pdf/mypdflib.php');

/*
 * library class for file submission plugin extending submission plugin
 * base class
 *
 * @package   mod_assign
 * @subpackage submission_pdf
 * @copyright 2012 Davo Smith
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_pdf extends assign_submission_plugin {

    /**
     * Get the name of the file submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('pdf', 'assignsubmission_pdf');
    }

    /**
     * Get file submission information from the database
     *
     * @global moodle_database $DB
     * @param int $submissionid
     * @return mixed
     */
    private function get_file_submission($submissionid) {
        global $DB;
        return $DB->get_record('assignsubmission_pdf', array('submission' => $submissionid));
    }

    /**
     * Get the default setting for file submission plugin
     * @global stdClass $CFG
     * @global stdClass $COURSE
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;

        $defaultmaxfilesubmissions = $this->get_config('maxfilesubmissions');
        $defaultmaxsubmissionsizebytes = $this->get_config('maxsubmissionsizebytes');

        $settings = array();
        $options = array();
        for ($i = 1; $i<=ASSIGNSUBMISSION_PDF_MAXFILES; $i++) {
            $options[$i] = $i;
        }

        $mform->addElement('select', 'assignsubmission_pdf_maxfiles', get_string('maxfilessubmission', 'assignsubmission_pdf'), $options);
        $mform->setDefault('assignsubmission_pdf_maxfiles', $defaultmaxfilesubmissions);
        $mform->disabledIf('assignsubmission_pdf_maxfiles', 'assignsubmission_pdf_enabled', 'eq', 0);

        $choices = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes);
        $choices[0] = get_string('courseuploadlimit').' ('.display_size($COURSE->maxbytes).')';
        $settings[] = array(
            'type' => 'select',
            'name' => 'maxsubmissionsizebytes',
            'description' => get_string('maximumsubmissionsize', 'assignsubmission_file'),
            'options' => $choices,
            'default' => $defaultmaxsubmissionsizebytes
        );

        $mform->addElement('select', 'assignsubmission_pdf_maxsizebytes', get_string('maximumsubmissionsize', 'assignsubmission_file'), $choices);
        $mform->setDefault('assignsubmission_pdf_maxsizebytes', $defaultmaxsubmissionsizebytes);
        $mform->disabledIf('assignsubmission_pdf_maxsizebytes', 'assignsubmission_pdf_enabled', 'eq', 0);

        $mform->addElement('filemanager', 'assignsubmission_pdf_coversheet', get_string('coversheet', 'assignsubmission_pdf'), null,
                           array(
                                'subdirs' => 0, 'maxbytes' => $COURSE->maxbytes,
                                'maxfiles' => 1, 'accepted_types' => array('*.pdf')
                           ));

        $mform->addElement('static', 'assignsubmission_pdf_template', '', 'Select template here');
        $mform->addElement('static', 'assignsubmission_pdf_template_edit', '', 'Edit templates here');
    }

    /**
     * Set up the draft file areas before displaying the settings form
     * @param array $default_values the values to be passed in to the form
     */
    public function data_preprocessing(&$default_values) {
        $context = $this->assignment->get_context();
        $course = $this->assignment->get_course();
        $draftitemid = file_get_submitted_draft_itemid('coversheet');
        if ($context) {
            // Not needed if the activty has not yet been created.
            file_prepare_draft_area($draftitemid, $context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_COVERSHEET, 0,
                                    array(
                                         'subdirs' => 0, 'maxbytes' => $course->maxbytes,
                                         'maxfiles' => 1, 'accepted_types' => array('*.pdf')
                                    ));
        }

        $default_values['assignsubmission_pdf_coversheet'] = $draftitemid;
    }

    /**
     * save the settings for file submission plugin
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        $this->set_config('maxfilesubmissions', $data->assignsubmission_pdf_maxfiles);
        $this->set_config('maxsubmissionsizebytes', $data->assignsubmission_pdf_maxsizebytes);

        $context = $this->assignment->get_context();
        $course = $this->assignment->get_course();
        file_save_draft_area_files($data->assignsubmission_pdf_coversheet, $context->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_COVERSHEET, 0,
                                   array(
                                        'subdirs' => 0, 'maxbytes' => $course->maxbytes,
                                        'maxfiles' => 1, 'accepted_types' => array('*.pdf')
                                   ));

        return true;
    }

    /**
     * File format options
     *
     * @return array
     */
    private function get_file_options() {
        $fileoptions = array(
            'subdirs' => 0,
            'maxbytes' => $this->get_config('maxsubmissionsizebytes'),
            'maxfiles' => $this->get_config('maxfilesubmissions'),
            'accepted_types' => array('*.pdf'),
            'return_types' => FILE_INTERNAL
        );
        return $fileoptions;
    }

    /**
     * Add elements to submission form
     *
     * @param mixed $submission stdClass|null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {
        if ($this->get_config('maxfilesubmissions')<=0) {
            return false;
        }

        $fileoptions = $this->get_file_options();
        $submissionid = $submission ? $submission->id : 0;

        file_prepare_standard_filemanager($data, 'pdfs', $fileoptions, $this->assignment->get_context(), 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_DRAFT, $submissionid);
        $mform->addElement('filemanager', 'pdfs_filemanager', '', null, $fileoptions);

        $mform->addElement('static', 'pdf_test', 'Hello', 'Fill in some details before submitting stuff (if there are templates)');

        return true;
    }

    /**
     * Count the number of files
     *
     * @param int $submissionid
     * @param string $area
     * @return int
     */
    private function count_files($submissionid, $area) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id, 'assignsubmission_pdf', $area, $submissionid, "id", false);

        return count($files);
    }

    /**
     * Save & preprocess the files and trigger plagiarism plugin, if enabled, to scan the uploaded files via events trigger
     *
     * @global stdClass $USER
     * @global moodle_database $DB
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB, $SESSION;

        // Pre-process all files to convert to useful PDF format
        $fileoptions = $this->get_file_options();

        file_postupdate_standard_filemanager($data, 'pdfs', $fileoptions, $this->assignment->get_context(), 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_DRAFT, $submission->id);

        $filesubmission = $this->get_file_submission($submission->id);

        //plagiarism code event trigger when files are uploaded

        $fs = get_file_storage();
        /** @var $files stored_file[] */
        $files = $fs->get_area_files($this->assignment->get_context()->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_DRAFT, $submission->id, "id", false);
        // Check all files are PDF v1.4 or less
        foreach ($files as $key => $file) {
            if (!AssignPDFLib::ensure_pdf_compatible($file)) {
                $filename = $file->get_filename();
                $file->delete();
                unset($files[$key]);
                if (!isset($SESSION->assignsubmission_pdf_invalid)) {
                    $SESSION->assignsubmission_pdf_invalid = array();
                }
                $SESSION->assignsubmission_pdf_invalid[] = $filename;
            }
        }

        $count = $this->count_files($submission->id, ASSIGNSUBMISSION_PDF_FA_DRAFT);
        // send files to event system
        // Let Moodle know that an assessable file was uploaded (eg for plagiarism detection)
        $eventdata = new stdClass();
        $eventdata->modulename = 'assign';
        $eventdata->cmid = $this->assignment->get_course_module()->id;
        $eventdata->itemid = $submission->id;
        $eventdata->courseid = $this->assignment->get_course()->id;
        $eventdata->userid = $USER->id;
        if ($count>1) {
            $eventdata->files = $files; // This is depreceated - please use pathnamehashes instead!
        }
        $eventdata->file = $files; // This is depreceated - please use pathnamehashes instead!
        $eventdata->pathnamehashes = array_keys($files);
        events_trigger('assessable_file_uploaded', $eventdata);

        if ($filesubmission) {
            $filesubmission->numfiles = $this->count_files($submission->id, ASSIGNSUBMISSION_PDF_FA_DRAFT);
            return $DB->update_record('assignsubmission_pdf', $filesubmission);
        } else {
            $filesubmission = new stdClass();
            $filesubmission->numfiles = $this->count_files($submission->id, ASSIGNSUBMISSION_PDF_FA_DRAFT);
            $filesubmission->submission = $submission->id;
            $filesubmission->assignment = $this->assignment->get_instance()->id;
            return $DB->insert_record('assignsubmission_pdf', $filesubmission)>0;
        }
    }

    /**
     * If a coversheet template is in use, make sure the student has supplied all
     * the data required.
     * @return bool|string 'true' if all data supplied, message if some data still needed
     */
    public function precheck_submission() {
        // TODO check coversheet data has been supplied
        return true;
    }

    /**
     * Combine the PDFs together ready for marking
     * @return void
     */
    public function submit_for_grading() {
        // TODO combine all the PDFs together, including the coversheet
    }

    /**
     * Produce a list of files suitable for export that represent this feedback or submission
     *
     * @param stdClass $submission The submission
     * @return array - return an array of files indexed by filename
     */
    public function get_files(stdClass $submission) {
        $result = array();
        $fs = get_file_storage();

        /** @var $files stored_file[] */
        $files = $fs->get_area_files($this->assignment->get_context()->id, 'assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_DRAFT, $submission->id, "timemodified", false);

        foreach ($files as $file) {
            $result[$file->get_filename()] = $file;
        }
        return $result;
    }

    /**
     * Display the list of files  in the submission status table
     * @param stdClass $submission
     * @param bool $showviewlink
     * @return string
     */
    public function view_summary(stdClass $submission, & $showviewlink) {
        global $SESSION;

        $output = '';
        if (isset($SESSION->assignsubmission_pdf_invalid)) {
            $invalidfiles = '';
            foreach ($SESSION->assignsubmission_pdf_invalid as $filename) {
                $invalidfiles .= html_writer::tag('p', get_string('invalidpdf', 'assignsubmission_pdf', $filename));
            }
            $output .= html_writer::tag('div', $invalidfiles, array('class' => 'assignsubmission_pdf_invalid'));
            unset($SESSION->assignsubmission_pdf_invalid);
        }
        $count = $this->count_files($submission->id, ASSIGNSUBMISSION_PDF_FA_DRAFT);
        $showviewlink = $count>ASSIGNSUBMISSION_PDF_MAXSUMMARYFILES;
        if ($showviewlink) {
            $output .= get_string('countfiles', 'assignsubmission_pdf', $count);
        } else {
            $output .= $this->assignment->render_area_files('assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_DRAFT, $submission->id);
        }

        return $output;
    }

    /**
     * No full submission view - the summary contains the list of files and that is the whole submission
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        return $this->assignment->render_area_files('assignsubmission_pdf', ASSIGNSUBMISSION_PDF_FA_DRAFT, $submission->id);
    }

    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type
     * and version.
     *
     * @param string $type
     * @param int $version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {
        // TODO return true for uploadpdf assignments
        /*
        if ($type == 'uploadpdf' && $version >= ?? 2011112900) {
            return true;
            }*/
        return false;
    }

    /**
     * Upgrade the settings from the old assignment
     * to the new plugin based one
     *
     * @param context $oldcontext - the old assignment context
     * @param stdClass $oldassignment - the old assignment data record
     * @param string $log record log events here
     * @return bool Was it a success? (false will trigger rollback)
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        // TODO upgrade uploadpdf settings
        /*
        if ($oldassignment->assignmenttype == 'uploadsingle') {
            $this->set_config('maxfilesubmissions', 1);
            $this->set_config('maxsubmissionsizebytes', $oldassignment->maxbytes);
            return true;
        }else {

            $this->set_config('maxfilesubmissions', $oldassignment->var1);
            $this->set_config('maxsubmissionsizebytes', $oldassignment->maxbytes);
            return true;
        }
        */
    }

    /**
     * Upgrade the submission from the old assignment to the new one
     *
     * @global moodle_database $DB
     * @param context $oldcontext The context of the old assignment
     * @param stdClass $oldassignment The data record for the old oldassignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $submission The data record for the new submission
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext, stdClass $oldassignment, stdClass $oldsubmission, stdClass $submission, & $log) {
        // TODO upgrade uploadpdf assignments

        /*
        global $DB;

        $filesubmission = new stdClass();

        $filesubmission->numfiles = $oldsubmission->numfiles;
        $filesubmission->submission = $submission->id;
        $filesubmission->assignment = $this->assignment->get_instance()->id;

        if (!$DB->insert_record('assignsubmission_pdf', $filesubmission) > 0) {
            $log .= get_string('couldnotconvertsubmission', 'assignsubmission_pdf', $submission->userid);
            return false;
        }




        // now copy the area files
        $this->assignment->copy_area_files_for_upgrade($oldcontext->id,
                                                        'mod_assignment',
                                                        'submission',
                                                        $oldsubmission->id,
                                                        // New file area
                                                        $this->assignment->get_context()->id,
                                                        'assignsubmission_pdf',
                                                        ASSIGNSUBMISSION_PDF_FA_DRAFT,
                                                        $submission->id);





        return true;
        */
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @global moodle_database $DB
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        // will throw exception on failure
        $DB->delete_records('assignsubmission_pdf', array('assignment' => $this->assignment->get_instance()->id));

        // TODO - lots of cleanup needed here

        return true;
    }

    /**
     * Formatting for log info
     * @param stdClass $submission The submission
     *
     * @return string
     */
    public function format_for_log(stdClass $submission) {
        // format the info for each submission plugin add_to_log
        $filecount = $this->count_files($submission->id, ASSIGNSUBMISSION_PDF_FA_DRAFT);
        $fileloginfo = '';
        $fileloginfo .= ' the number of file(s) : '.$filecount." file(s).<br>";

        return $fileloginfo;
    }

    /**
     * Return true if there are no submission files
     */
    public function is_empty(stdClass $submission) {
        return $this->count_files($submission->id, ASSIGNSUBMISSION_PDF_FA_DRAFT) == 0;
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        $name = $this->get_name();
        return array(
            ASSIGNSUBMISSION_PDF_FA_COVERSHEET => $name,
            ASSIGNSUBMISSION_PDF_FA_DRAFT => $name,
            ASSIGNSUBMISSION_PDF_FA_FINAL => $name
        );
    }
}
