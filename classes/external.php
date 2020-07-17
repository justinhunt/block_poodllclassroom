<?php


/**
 * External class.
 *
 * @package block_poollclassroom
 * @author  Justin Hunt - Poodll.com
 */

use \block_poodllclassroom\common;
use \block_poodllclassroom\constants;

class block_poodllclassroom_external extends external_api {


    public static function submit_mform_parameters() {
        return new external_function_parameters(
                array(
                        'contextid' => new external_value(PARAM_INT, 'The context id for the course'),
                        'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array'),
                        'formname' => new external_value(PARAM_TEXT, 'The formname')
                )
        );
    }

    public static function submit_mform($contextid,$jsonformdata, $formname) {
        global $CFG, $DB, $USER;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_mform_parameters(),
                ['contextid' => $contextid, 'jsonformdata' => $jsonformdata, 'formname'=>$formname]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        list($ignored, $course) = get_context_info_array($context->id);
        $serialiseddata = json_decode($params['jsonformdata']);

        $data = array();
        parse_str($serialiseddata, $data);

        switch($formname){
            case 'creategroup':
                    require_capability('moodle/course:managegroups', $context);
                    require_once($CFG->dirroot . '/group/lib.php');
                    require_once($CFG->dirroot . '/group/group_form.php');
                    $editoroptions = [
                            'maxfiles' => EDITOR_UNLIMITED_FILES,
                            'maxbytes' => $course->maxbytes,
                            'trust' => false,
                            'context' => $context,
                            'noclean' => true,
                            'subdirs' => false
                    ];
                    $group = new stdClass();
                    $group->courseid = $course->id;
                    $group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);

                    // The last param is the ajax submitted data.
                    $mform = new group_form(null, array('editoroptions' => $editoroptions), 'post', '', null, true, $data);

                    $validateddata = $mform->get_data();

                    if ($validateddata) {
                        // Do the action.
                        $groupid = groups_create_group($validateddata, $mform, $editoroptions);
                    } else {
                        // Generate a warning.
                        throw new moodle_exception('erroreditgroup', 'group');
                    }

                    return $groupid;
                break;

            case 'createcourse':
                require_once($CFG->dirroot .'/blocks/iomad_company_admin/lib.php');
error_log('going into create course');
                $systemcontext = context_system::instance();
                iomad::require_capability('block/iomad_company_admin:createcourse', $systemcontext);
error_log('got capability');


                // Correct the navbar.
                // Set the name for the page.
                $linktext = get_string('createcourse_title', 'block_iomad_company_admin');
                // Set the url.
                $linkurl = new moodle_url('/blocks/iomad_company_admin/company_course_create_form.php');



                // Set the companyid
                $companyid = iomad::get_my_companyid($context);
 error_log('got company id:' . $companyid );

                /* next line copied from /course/edit.php */
                $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES,
                        'maxbytes' => $CFG->maxbytes,
                        'trusttext' => false,
                        'noclean' => true);

                $mform = new \block_poodllclassroom\local\form\createcourseform(null, array('companyid'=>$companyid,'editoroptions'=>$editoroptions), $data);
error_log('got form:'  );
                $validateddata = $mform->get_data();
error_log('got data:'  );
                if ($validateddata) {
                    $validateddata->userid = $USER->id;

                    // Merge data with course defaults.
                    $company = $DB->get_record('company', array('id' => $companyid));
                    if (!empty($company->category)) {
                        $validateddata->category = $company->category;
                    } else {
                        $validateddata->category = $CFG->defaultrequestcategory;
                    }
                    $courseconfig = get_config('moodlecourse');
                    $mergeddata = (object) array_merge((array) $courseconfig, (array) $validateddata);

                    // Turn on restricted modules.
                    $mergeddata->restrictmodules = 1;
 error_log('creating course:'  );
                    if (!$course = create_course($mergeddata, $editoroptions)) {
error_log('failed miserably:'  );
                        return 'error';
                        /*
                        $this->verbose("Error inserting a new course in the database!");
                        if (!$this->get('ignore_errors')) {
                            die();
                        }
                        */
                    }

                    // If licensed course, turn off all enrolments apart from license enrolment as
                    // default  Moving this to a separate page.
                    if ($validateddata->selfenrol == 0 ) {
                        if ($instances = $DB->get_records('enrol', array('courseid' => $course->id))) {
                            foreach ($instances as $instance) {
                                $updateinstance = (array) $instance;
                                if ($instance->enrol == 'self') {
                                    $updateinstance['status'] = 0;
                                } else if ($instance->enrol == 'license') {
                                    $updateinstance['status'] = 1;
                                } else if ($instance->enrol == 'manual') {
                                    $updateinstance['status'] = 0;
                                }
                                $DB->update_record('enrol', $updateinstance);
                            }
                        }
                    } else if ($validateddata->selfenrol == 1 ) {
                        if ($instances = $DB->get_records('enrol', array('courseid' => $course->id))) {
                            foreach ($instances as $instance) {
                                $updateinstance = (array) $instance;
                                if ($instance->enrol == 'self') {
                                    $updateinstance['status'] = 1;
                                } else if ($instance->enrol == 'license') {
                                    $updateinstance['status'] = 1;
                                } else if ($instance->enrol == 'manual') {
                                    $updateinstance['status'] = 0;
                                }
                                $DB->update_record('enrol', $updateinstance);
                            }
                        }
                    } else if ($validateddata->selfenrol == 2 ) {
                        if ($instances = $DB->get_records('enrol', array('courseid' => $course->id))) {
                            foreach ($instances as $instance) {
                                $updateinstance = (array) $instance;
                                if ($instance->enrol == 'self') {
                                    $updateinstance['status'] = 1;
                                } else if ($instance->enrol == 'license') {
                                    $updateinstance['status'] = 0;
                                } else if ($instance->enrol == 'manual') {
                                    $updateinstance['status'] = 1;
                                }
                                $DB->update_record('enrol', $updateinstance);
                            }
                        }
                    }

                    // Associate the company with the course.
                    $company = new company($companyid);
                    // Check if we are a company manager.
                    if ($validateddata->selfenrol != 2 && $DB->get_record('company_users', array('companyid' => $companyid,
                                    'userid' => $USER->id,
                                    'managertype' => 1))) {
                        $company->add_course($course, 0, true);
                    } else if ($validateddata->selfenrol == 2) {
                        $company->add_course($course, 0, false, true);
                    } else {
                        $company->add_course($course);
                    }

                    return $course->id;
                }

                break;

        }


    }


    public static function submit_mform_returns() {
        return new external_value(PARAM_RAW);
        //return new external_value(PARAM_INT, 'group id');
    }

}
