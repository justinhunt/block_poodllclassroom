<?php


/**
 * External class.
 *
 * @package block_poollclassroom
 * @author  Justin Hunt - Poodll.com
 */

use \block_poodllclassroom\common;
use \block_poodllclassroom\constants;
use \block_poodllclassroom\chargebee_helper;
use \block_poodllclassroom\standardadaptor;

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

        $ret = standardadaptor::update_sub($params);
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

        $ret = standardadaptor::resume_sub($params);
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

        $ret = standardadaptor::pause_sub($params);

    }

    public static function pause_sub_returns() {
        return new external_single_structure([
                'message' => new external_value(PARAM_TEXT, 'error message'),
                'error' => new external_value(PARAM_BOOL, 'error'),
        ]);
    }

    //------------ REACTIVATE SUB ---------------//
    public static function reactivate_sub_parameters() {
        return new external_function_parameters(
                array(
                        'upstreamplanid' => new external_value(PARAM_TEXT, 'upstreamplanid'),
                        'upstreamownerid' => new external_value(PARAM_TEXT, 'upstreamownerid'),
                        'upstreamsubid' => new external_value(PARAM_TEXT, 'upstreamsubid')

                )
        );
    }

    public static function reactivate_sub( $upstreamplanid, $upstreamownerid,$upstreamsubid) {
        global $CFG, $SESSION, $DB, $USER;

        // Get/check context/capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:manageintegration', $context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::reactivate_sub_parameters(),
                ['upstreamplanid' => $upstreamplanid,
                        'upstreamownerid' => $upstreamownerid,
                        'upstreamsubid' => $upstreamsubid]);

        $ret = standardadaptor::reactivate_sub($params);

    }

    public static function reactivate_sub_returns() {
        return new external_single_structure([
                'message' => new external_value(PARAM_TEXT, 'error message'),
                'error' => new external_value(PARAM_BOOL, 'error'),
        ]);
    }

    //------------ ACTIVATE SUB ---------------//
    public static function activate_sub_parameters() {
        return new external_function_parameters(
                array(
                        'upstreamplanid' => new external_value(PARAM_TEXT, 'upstreamplanid'),
                        'upstreamownerid' => new external_value(PARAM_TEXT, 'upstreamownerid'),
                        'upstreamsubid' => new external_value(PARAM_TEXT, 'upstreamsubid')

                )
        );
    }

    public static function activate_sub( $upstreamplanid, $upstreamownerid,$upstreamsubid) {
        global $CFG, $SESSION, $DB, $USER;

        // Get/check context/capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:manageintegration', $context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::activate_sub_parameters(),
                ['upstreamplanid' => $upstreamplanid,
                        'upstreamownerid' => $upstreamownerid,
                        'upstreamsubid' => $upstreamsubid]);

        $ret = standardadaptor::activate_sub($params);

    }

    public static function activate_sub_returns() {
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

        $ret = standardadaptor::update_sub($params);
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

        $ret = standardadaptor::create_sub($params);

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


        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::delete_item_parameters(),
            ['contextid' => $contextid, 'itemid' => $itemid, 'formname' => $formname]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        switch($formname){
            case constants::FORM_DELETEUSER:
               // $result =  common::remove_user_from_company($context,$company,$itemid);
                break;

            case constants::FORM_DELETECOURSE:
              //$result =  common::remove_course_from_company($context,$companyid,$itemid);
               // $result =  common::delete_course_from_company($companyid,$itemid);
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



                $usercontext = context_user::instance($USER->id);
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

                   // $ret = common::update_company_user($companyid,$usernew,$user,$editoroptions);
                    return json_encode('{}');
                }
                break;

            case constants::FORM_CREATEUSER:

                require_once($CFG->dirroot . '/user/editlib.php');


                $systemcontext = context_system::instance();

/*
                $mform = new \block_poodllclassroom\local\form\createuserform($companyid,  $data);
                $validateddata = $mform->get_data();
                if ($validateddata) {
                    $ret = common::create_company_user($companyid,$validateddata);
                    return json_encode($ret);
                }
*/
                return json_encode([]);
                break;

            case constants::FORM_CREATECOURSE:
            case constants::FORM_EDITCOURSE:
                require_once($CFG->dirroot . '/course/lib.php');


                $systemcontext = context_system::instance();



                /* next line copied from /course/edit.php */
                $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES,
                        'maxbytes' => $CFG->maxbytes,
                        'trusttext' => false,
                        'noclean' => true);

                $method='post';
                $target='';
                $attributes=null;
                $editable=true;
                $mform = new \block_poodllclassroom\local\form\createcourseform(null, array('schoolid'=>0,'editoroptions'=>$editoroptions),
                        $method,$target,$attributes,$editable,$data);

                $validateddata = $mform->get_data();
                if ($validateddata) {
                    //error_log(print_r( $validateddata, true ));
                   // $ret = common::upsert_company_course(0,$validateddata,$editoroptions, $formname);
                    return json_encode([]);
                }
                break;

            case constants::FORM_UPLOADUSER:
                $schoolid = 1;
                $mform = new \block_poodllclassroom\local\form\uploaduserform($schoolid, $data);
                $validateddata = $mform->get_data();
                if ($validateddata) {
                    if (!empty($validateddata->importdata)) {

                        //get delimiter
                        switch($validateddata->delimiter){
                            case 'delim_comma': $delimiter = ',';break;
                            case 'delim_pipe': $delimiter = '|';break;
                            case 'delim_tab':
                            default:
                                $delimiter ="\t";
                        }

                        //get array of rows
                        $rawdata =trim($validateddata->importdata);
                        $rows = explode(PHP_EOL, $rawdata);

                        //prepare results fields
                        $imported = 0;
                        $failed = array();

                        foreach($rows as $row){
                            $cols = explode($delimiter,$row);
                            if(count($cols)>=3 && !empty($cols[0]) && !empty($cols[1])&& !empty($cols[2])){
                                $userrow = new \stdClass();
                                $userrow->schoolid=$schoolid;
                                $userrow->user_email_as_username=false;
                                $userrow->due=0; //needed for email of password, might need to be set more properly
                                $userrow->firstname=$cols[0];
                                $userrow->lastname=$cols[1];
                                $userrow->email=$cols[2];
                                if(count($cols)>3&&!empty($cols[3])){
                                    $userrow->newpassword=$cols[3];
                                }

                                $ret = true;//common::create_company_user($companyid,$userrow);
                                if(!$ret || $ret->error){
                                    $failed[]=$row;
                                }else {
                                    $imported++;
                                }
                            }else{
                                $failed[]=$row;
                            }//end of if cols ok
                        }//end of for each


                        // Uncomment when migrating to 3.1.
                        // redirect($PAGE->url, get_string('termadded', 'mod_wordcards', $data->term));
                        $result=new stdClass();
                        $result->imported=$imported;
                        $result->failed=count($failed);
                        $message=get_string('importresults',constants::M_COMP,$result);

                        $ret = new \stdClass();
                        $ret->schoolid=$schoolid;
                        $ret->message='';
                        $ret->error=false;

                        if(count($failed)>0){
                            $leftoverrows = implode(PHP_EOL,$failed);
                            $ret->delimiter=$validateddata->delimiter;
                            $ret->importdata=$leftoverrows;
                            $ret->message=$message . ' ' . get_string('returnedrows',constants::M_COMP); ;
                            $ret->error=true;
                        }
                        return json_encode($ret);

                    }
                }


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
                  'planid' => new external_value(PARAM_TEXT, 'The plan id '),
                  'schoolid' => new external_value(PARAM_TEXT, 'The school id '),
                  'currentsubid' => new external_value(PARAM_INT, 'The current sub id ')
                )
        );
    }

    public static function get_checkout_existing($planid, $schoolid, $currentsubid) {

        // Get/check context/capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:usepoodllclassroom', $context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::get_checkout_existing_parameters(),
                ['planid' => $planid, 'schoolid' => $schoolid, 'currentsubid' => $currentsubid]);

        $hosted_page = chargebee_helper::get_checkout_existing($params['planid'],$params['schoolid'],$params['currentsubid']);
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

    //------------ get_checkout_new ---------------//
    public static function get_checkout_new_parameters() {
        return new external_function_parameters(
            array(
                'planid' => new external_value(PARAM_INT, 'The plan id for this subscription'),
                'currency' => new external_value(PARAM_TEXT, 'The currency for this subscription'),
                'billinginterval' => new external_value(PARAM_INT, 'The billing interval for this subscription'),
                'schoolid' => new external_value(PARAM_INT, 'The schoolid for this subscription')
            )
        );
    }

    public static function get_checkout_new($planid, $currency, $billinginterval,$schoolid) {

        // Get/check context/capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:usepoodllclassroom', $context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::get_checkout_new_parameters(),
            ['planid' => $planid, 'currency'=>$currency, 'billinginterval'=>$billinginterval,'schoolid'=>$schoolid]);

        $hosted_page = chargebee_helper::get_checkout_new($params['planid'],$params['currency'],$params['billinginterval'], $params['schoolid']);
        if($hosted_page){
            $ret =$hosted_page->hosted_page;
        }else{
            $ret ='{}';
        }
        return $ret;
    }

    public static function get_checkout_new_returns() {

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

    //------------ create_portal_session ---------------//
    public static function create_portal_session_parameters() {
        return new external_function_parameters(
            array(
                'upstreamownerid' => new external_value(PARAM_TEXT, 'The upstreamownerid'),
            )
        );
    }

    public static function create_portal_session($upstreamownerid) {

        // Get/check context/capability
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:usepoodllclassroom', $context);

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::create_portal_session_parameters(),
            ['upstreamownerid' => $upstreamownerid]);

        $portal_session = chargebee_helper::create_portal_session($params['upstreamownerid']);
        if($portal_session){
            $ret =$portal_session;
        }else{
            $ret ='{}';
        }
        return $ret;
    }

    public static function create_portal_session_returns() {

        // return new external_value(PARAM_RAW);
        return new external_single_structure([
            'id' => new external_value(PARAM_TEXT, 'id'),
            'token' => new external_value(PARAM_TEXT, 'token'),
            'access_url' => new external_value(PARAM_URL, 'url'),
            'status' => new external_value(PARAM_TEXT, 'status'),
            'created_at' => new external_value(PARAM_INT, 'created at'),
            'expires_at' => new external_value(PARAM_INT, 'expires at'),
            'object' => new external_value(PARAM_TEXT, 'object'),
            'customer_id' => new external_value(PARAM_TEXT, 'customer id')
        ]);
    }
}
