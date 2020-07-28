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

    public static function delete_item_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'The context id for the course'),
                'itemid' => new external_value(PARAM_INT, 'The itemid to delete'),
                'formname' => new external_value(PARAM_TEXT, 'The formname')
            )
        );
    }

    public static function delete_item($contextid,$itemid, $formname)
    {
        global $CFG, $DB, $USER;

        require_once($CFG->dirroot . '/blocks/iomad_company_admin/lib.php');

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::delete_item_parameters(),
            ['contextid' => $contextid, 'itemid' => $itemid, 'formname' => $formname]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        $ret = new \stdClass();
        $ret->itemid=$itemid;
        $ret->error=false;
        return json_encode($ret);
    }

    public static function delete_item_returns() {
        return new external_value(PARAM_RAW);
        //return new external_value(PARAM_INT, 'group id');
    }

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

        require_once($CFG->dirroot .'/blocks/iomad_company_admin/lib.php');

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
            case 'createuser':

                require_once($CFG->dirroot . '/user/editlib.php');


                $systemcontext = context_system::instance();
                iomad::require_capability('block/iomad_company_admin:user_create', $systemcontext);
                
                //Set the companyid
                $companyid = iomad::get_my_companyid($context);
                $company = new company($companyid);
                
                // Check if the company has gone over the user quota.
                if (!$company->check_usercount(1)) {
                    $dashboardurl = new moodle_url('/my');
                    $maxusers = $company->get('maxusers');
                    print_error('maxuserswarning', 'block_iomad_company_admin', $dashboardurl, $maxusers);
                }
                $departmentid=0;
                $licenseid=0;
                $mform = new \block_poodllclassroom\local\form\createuserform($companyid, $departmentid, $licenseid, $data);
                $validateddata = $mform->get_data();
                if ($validateddata) {

                    // Trim first and lastnames
                    $validateddata->firstname = trim($validateddata->firstname);
                    $validateddata->lastname = trim($validateddata->lastname);

                    $validateddata->userid = $USER->id;
                    if ($companyid > 0) {
                        $validateddata->companyid = $companyid;
                    }
//error_log(print_r( $validateddata, true ));

                    if (!$userid = company_user::create($validateddata)) {
                        return 'error';
                        /*
                        $this->verbose("Error inserting a new user in the database!");
                        if (!$this->get('ignore_errors')) {
                            die();
                        }
                        */
                    }
                    $user = new stdclass();
                    $user->id = $userid;
                    $validateddata->id = $userid;

                    // Save custom profile fields data.
                    profile_save_data($validateddata);

                    $systemcontext = context_system::instance();

                    // Check if we are assigning a different role to the user.
                    if (!empty($validateddata->managertype || !empty($validateddata->educator))) {
                        company::upsert_company_user($userid, $companyid, $validateddata->userdepartment, $validateddata->managertype,
                                $validateddata->educator);
                    }

                    // Assign the user to the default company department.
                    $parentnode = company::get_company_parentnode($companyid);
                    if (iomad::has_capability('block/iomad_company_admin:edit_all_departments', $systemcontext)) {
                        $userhierarchylevel = $parentnode->id;
                    } else {
                        $userlevel = $company->get_userlevel($USER);
                        $userhierarchylevel = $userlevel->id;
                    }
                    company::assign_user_to_department($validateddata->userdepartment, $userid);

                    // Enrol the user on the courses.
                    if (!empty($createcourses)) {
                        $userdata = $DB->get_record('user', array('id' => $userid));
                        company_user::enrol($userdata, $createcourses, $companyid);
                    }
                    // Assign and licenses.
                    if (!empty($licenseid)) {
                        $licenserecord = (array) $DB->get_record('companylicense', array('id' => $licenseid));
                        if (!empty($licenserecord['program'])) {
                            // If so the courses are not passed automatically.
                            $validateddata->licensecourses = $DB->get_records_sql_menu("SELECT c.id, clc.courseid FROM {companylicense_courses} clc
                                                                   JOIN {course} c ON (clc.courseid = c.id
                                                                   AND clc.licenseid = :licenseid)",
                                    array('licenseid' => $licenserecord['id']));
                        }

                        if (!empty($validateddata->licensecourses)) {
                            $userdata = $DB->get_record('user', array('id' => $userid));
                            $count = $licenserecord['used'];
                            $numberoflicenses = $licenserecord['allocation'];
                            foreach ($validateddata->licensecourses as $licensecourse) {
                                if ($count >= $numberoflicenses) {
                                    // Set the used amount.
                                    $licenserecord['used'] = $count;
                                    $DB->update_record('companylicense', $licenserecord);
                                    redirect(new moodle_url("/blocks/iomad_company_admin/company_license_users_form.php",
                                            array('licenseid' => $licenseid, 'error' => 1)));
                                }

                                $issuedate = time();
                                $DB->insert_record('companylicense_users',
                                        array('userid' => $userdata->id,
                                                'licenseid' => $licenseid,
                                                'issuedate' => $issuedate,
                                                'licensecourseid' => $licensecourse));

                                // Create an event.
                                $eventother = array('licenseid' => $licenseid,
                                        'issuedate' => $issuedate,
                                        'duedate' => $validateddata->due);
                                $event =
                                        \block_iomad_company_admin\event\user_license_assigned::create(array('context' => context_course::instance($licensecourse),
                                                'objectid' => $licenseid,
                                                'courseid' => $licensecourse,
                                                'userid' => $userdata->id,
                                                'other' => $eventother));
                                $event->trigger();
                                $count++;
                            }
                        }
                    }

                    return $userid;

                }

                break;

            case 'createcourse':
                require_once($CFG->dirroot . '/course/lib.php');


                $systemcontext = context_system::instance();
                iomad::require_capability('block/iomad_company_admin:createcourse', $systemcontext);


                // Correct the navbar.
                // Set the name for the page.
                $linktext = get_string('createcourse_title', 'block_iomad_company_admin');
                // Set the url.
                $linkurl = new moodle_url('/blocks/iomad_company_admin/company_course_create_form.php');



                // Set the companyid
                $companyid = iomad::get_my_companyid($context);


                /* next line copied from /course/edit.php */
                $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES,
                        'maxbytes' => $CFG->maxbytes,
                        'trusttext' => false,
                        'noclean' => true);

                $method='post';
                $target='';
                $attributes=null;
                $editable=true;
                $mform = new \block_poodllclassroom\local\form\createcourseform(null, array('companyid'=>$companyid,'editoroptions'=>$editoroptions),
                        $method,$target,$attributes,$editable,$data);

                $validateddata = $mform->get_data();
                if ($validateddata) {
//error_log(print_r( $validateddata, true ));

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
                    if (!$course = create_course($mergeddata, $editoroptions)) {
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
