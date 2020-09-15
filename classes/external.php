<?php


/**
 * External class.
 *
 * @package block_poollclassroom
 * @author  Justin Hunt - Poodll.com
 */

use \block_poodllclassroom\common;
use \block_poodllclassroom\constants;
use \block_poodllclassroom\iomadadaptor;

class block_poodllclassroom_external extends external_api {

    //------------ CANCEL SUB ---------------//
    public static function cancel_sub_parameters() {
        return new external_function_parameters(
                array(
                        'upstreamplanid' => new external_value(PARAM_TEXT, 'upstreamplanid'),
                        'upstreamownerid' => new external_value(PARAM_TEXT, 'upstreamownerid'),
                        'upstreamsubid' => new external_value(PARAM_TEXT, 'upstreamsubid')

                )
        );
    }

    public static function cancel_sub( $upstreamplanid, $upstreamownerid,$upstreamsubid) {
        global $CFG, $SESSION, $DB, $USER;

        // Get/check context/capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:manageintegration', $context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::cancel_sub_parameters(),
                ['upstreamplanid' => $upstreamplanid,
                        'upstreamownerid' => $upstreamownerid,
                        'upstreamsubid' => $upstreamsubid]);

        $ret = iomadadaptor::update_sub($params);
        return $ret;
    }

    public static function cancel_sub_returns() {
        return new external_single_structure([
                'message' => new external_value(PARAM_TEXT, 'error message'),
                'error' => new external_value(PARAM_BOOL, 'error'),
        ]);
    }


    //------------ RESUME SUB ---------------//
    public static function resume_sub_parameters() {
        return new external_function_parameters(
                array(
                        'upstreamplanid' => new external_value(PARAM_TEXT, 'upstreamplanid'),
                        'upstreamownerid' => new external_value(PARAM_TEXT, 'upstreamownerid'),
                        'upstreamsubid' => new external_value(PARAM_TEXT, 'upstreamsubid')

                )
        );
    }

    public static function resume_sub( $upstreamplanid, $upstreamownerid,$upstreamsubid) {

        global $CFG, $SESSION, $DB, $USER;

        // Get/check context/capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:manageintegration', $context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::resume_sub_parameters(),
                ['upstreamplanid' => $upstreamplanid,
                        'upstreamownerid' => $upstreamownerid,
                        'upstreamsubid' => $upstreamsubid]);

        $ret = iomadadaptor::resume_sub($params);
        return $ret;
    }

    public static function resume_sub_returns() {
        return new external_single_structure([
                'message' => new external_value(PARAM_TEXT, 'error message'),
                'error' => new external_value(PARAM_BOOL, 'error'),
        ]);
    }


    //------------ PAUSE SUB ---------------//
    public static function pause_sub_parameters() {
        return new external_function_parameters(
                array(
                        'upstreamplanid' => new external_value(PARAM_TEXT, 'upstreamplanid'),
                        'upstreamownerid' => new external_value(PARAM_TEXT, 'upstreamownerid'),
                        'upstreamsubid' => new external_value(PARAM_TEXT, 'upstreamsubid')

                )
        );
    }

    public static function pause_sub( $upstreamplanid, $upstreamownerid,$upstreamsubid) {
        global $CFG, $SESSION, $DB, $USER;

        // Get/check context/capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:manageintegration', $context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::pause_sub_parameters(),
                ['upstreamplanid' => $upstreamplanid,
                        'upstreamownerid' => $upstreamownerid,
                        'upstreamsubid' => $upstreamsubid]);

        $ret = iomadadaptor::pause_sub($params);

    }

    public static function pause_sub_returns() {
        return new external_single_structure([
                'message' => new external_value(PARAM_TEXT, 'error message'),
                'error' => new external_value(PARAM_BOOL, 'error'),
        ]);
    }

    //------------ UPDATE SUB ---------------//
    public static function update_sub_parameters() {
        return new external_function_parameters(
                array(
                        'upstreamplanid' => new external_value(PARAM_TEXT, 'upstreamplanid'),
                        'upstreamownerid' => new external_value(PARAM_TEXT, 'upstreamownerid'),
                        'upstreamsubid' => new external_value(PARAM_TEXT, 'upstreamsubid')

                )
        );
    }

    public static function update_sub( $upstreamplanid, $upstreamownerid,$upstreamsubid) {
        global $CFG, $SESSION, $DB, $USER;

        // Get/check context/capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:manageintegration', $context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::update_sub_parameters(),
                ['upstreamplanid' => $upstreamplanid,
                 'upstreamownerid' => $upstreamownerid,
                 'upstreamsubid' => $upstreamsubid]);

        $ret = iomadadaptor::update_sub($params);
        return $ret;
    }

    public static function update_sub_returns() {
        return new external_single_structure([
                'message' => new external_value(PARAM_TEXT, 'error message'),
                'error' => new external_value(PARAM_BOOL, 'error'),
        ]);
    }

    //------------ CREATE SUB ---------------//
    public static function create_sub_parameters() {
        return new external_function_parameters(
            array(
                'username' => new external_value(PARAM_TEXT, 'User name'),
                'firstname' => new external_value(PARAM_TEXT, 'User first name'),
                'lastname' => new external_value(PARAM_TEXT, 'User last name'),
                'email' => new external_value(PARAM_EMAIL, 'Email'),
                'schoolname' => new external_value(PARAM_TEXT, 'School name'),
                'upstreamplanid' => new external_value(PARAM_TEXT, 'upstreamplanid'),
                'upstreamownerid' => new external_value(PARAM_TEXT, 'upstreamownerid'),
                'upstreamsubid' => new external_value(PARAM_TEXT, 'upstreamsubid')

            )
        );
    }

    public static function create_sub($username,$firstname, $lastname,$email,$schoolname,
            $upstreamplanid, $upstreamownerid,$upstreamsubid)
    {
        global $CFG,$SESSION, $DB, $USER;


        // Get/check context/capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:manageintegration', $context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::create_sub_parameters(),
            ['username' => $username, 'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email, 'schoolname'=>$schoolname,
                    'upstreamplanid'=>$upstreamplanid,'upstreamownerid'=>$upstreamownerid,'upstreamsubid'=>$upstreamsubid]);

        $ret = iomadadaptor::create_sub($params);

        return $ret;
    }

    public static function create_sub_returns() {
        //return new external_value(PARAM_RAW);
        //return new external_value(PARAM_INT, 'group id');
        return new external_single_structure([
                'schoolid' => new external_value(PARAM_INT, 'school id'),
                'userid' => new external_value(PARAM_INT, 'user id' ),
                'username' => new external_value(PARAM_TEXT, 'user name'),
                'message' => new external_value(PARAM_TEXT, 'error message'),
                'error' => new external_value(PARAM_BOOL, 'error'),
        ]);
    }

    //------------ DELETE ITEM ---------------//
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

        //Set the companyid
        $companyid = iomad::get_my_companyid($context);
        $company = new company($companyid);

        switch($formname){
            case constants::FORM_DELETEUSER:
                $result =  common::remove_user_from_company($context,$company,$itemid);
                break;

            case constants::FORM_DELETECOURSE:
              $result =  common::remove_course_from_company($context,$companyid,$itemid);
              break;
        }

        $ret = new \stdClass();
        $ret->itemid=$itemid;
        $ret->error=false;
        return json_encode($ret);
    }

    public static function delete_item_returns() {
        return new external_value(PARAM_RAW);
        //return new external_value(PARAM_INT, 'group id');
    }


    //------------ SUBMIT MFORM ---------------//
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

            case constants::FORM_EDITUSER:
                require_once($CFG->dirroot . '/user/editlib.php');
                $systemcontext = context_system::instance();
                iomad::require_capability('block/iomad_company_admin:user_create', $systemcontext);

                //Set the companyid
                $companyid = iomad::get_my_companyid($context);
                $company = new company($companyid);
                $departmentid=0;

                //not sure about this
                $userid = $data['id'];

                // Editing existing user.
                iomad::require_capability('block/iomad_company_admin:editusers', $systemcontext);
                if (!$user = $DB->get_record('user', array('id' => $userid))) {
                    print_error('invaliduserid',constants::M_COMP);
                }
                if (!company::check_canedit_user($companyid, $userid)) {
                    print_error('invaliduserid', constants::M_COMP);
                }

                if (!empty($userid) && !company::check_valid_user($companyid, $userid, $departmentid)) {
                    print_error('invaliduserdepartment', constants::M_COMP);
                }

                // Remote users cannot be edited.
                if ($user->id != -1 and is_mnet_remote_user($user)) {
                    print_error('canteditremoteusers');
                }

                if (isguestuser($user->id)) { // The real guest user can not be edited.
                    print_error('guestnoeditprofileother');
                }


                $usercontext = context_user::instance($user->id);
                $editoroptions = array(
                    'maxfiles'   => EDITOR_UNLIMITED_FILES,
                    'maxbytes'   => $CFG->maxbytes,
                    'trusttext'  => false,
                    'forcehttps' => false,
                    'context'    => $usercontext
                );

                $filemanagercontext = $editoroptions['context'];
                $filemanageroptions = array('maxbytes'       => $CFG->maxbytes,
                    'subdirs'        => 0,
                    'maxfiles'       => 1,
                    'accepted_types' => 'web_image',
                    'context'=>$filemanagercontext);
                $method='post';
                $target='';
                $attributes=null;
                $editable=true;
                $mform = new \block_poodllclassroom\local\form\edituserform(null, array('editoroptions'=>$editoroptions,'filenamanageroptions'=>$filemanageroptions),
                    $method,$target,$attributes,$editable,$data);
                $usernew = $mform->get_data();
                if ($usernew) {

                    $ret = common::update_company_user($companyid,$usernew,$user,$editoroptions);
                    return json_encode($ret);
                }
                break;

            case constants::FORM_CREATEUSER:

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

                    $ret = common::create_company_user($companyid,$validateddata);
                    return json_encode($ret);
                }

                break;

            case constants::FORM_CREATECOURSE:
            case constants::FORM_EDITCOURSE:
                require_once($CFG->dirroot . '/course/lib.php');


                $systemcontext = context_system::instance();
                iomad::require_capability('block/iomad_company_admin:createcourse', $systemcontext);

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
                    $ret = common::upsert_company_course($companyid,$validateddata,$editoroptions, $formname);
                    return json_encode($ret);
                }
                break;

        }
    }


    public static function submit_mform_returns() {
        return new external_value(PARAM_RAW);
        //return new external_value(PARAM_INT, 'group id');
    }


    //------------ get_checkout_existing ---------------//
    public static function get_checkout_existing_parameters() {
        return new external_function_parameters(
                array(
                  'planid' => new external_value(PARAM_INT, 'The change-to plan id for this subscripton')
                )
        );
    }

    public static function get_checkout_existing($planid) {

        // Get/check context/capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:managepoodllclassroom', $context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::get_checkout_existing_parameters(),
                ['planid' => $planid]);

        $hosted_page = common::get_checkout_existing($params['planid']);
        if($hosted_page){
            $ret =$hosted_page->hosted_page;
        }else{
            $ret ='{}';
        }
        return $ret;
    }

    public static function get_checkout_existing_returns() {

       // return new external_value(PARAM_RAW);
        return new external_single_structure([
                'created_at' => new external_value(PARAM_INT, 'created at'),
                'embed' => new external_value(PARAM_BOOL, 'embed' ),
                'expires_at' => new external_value(PARAM_INT, 'expires at'),
                'id' => new external_value(PARAM_TEXT, 'id'),
                'object' => new external_value(PARAM_TEXT, 'object'),
                'resource_version' => new external_value(PARAM_INT, 'resource version'),
                'state' => new external_value(PARAM_TEXT, 'state'),
                'type' => new external_value(PARAM_TEXT, 'type'),
                'updated_at' => new external_value(PARAM_INT, 'updated at'),
                'url' => new external_value(PARAM_TEXT, 'url'),
        ]);
    }
}
