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

        //initialise return value
        $ret = [];
        $ret['error'] = false;
        $ret['message'] = '';


        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::cancel_sub_parameters(),
                ['upstreamplanid' => $upstreamplanid,
                        'upstreamownerid' => $upstreamownerid,
                        'upstreamsubid' => $upstreamsubid]);

        //Get Poodll School and once its got, cancel it
        $poodllschool =common::get_poodllschool_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::cancel_poodllschool($poodllschool);
            $ret['message'] = 'School cancelled ok';
        }else{
            $ret['error'] = true;
            $ret['message'] = 'No such school was found';
        }
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


        //initialise return value
        $ret = [];
        $ret['error'] = false;
        $ret['message'] = '';


        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::resume_sub_parameters(),
                ['upstreamplanid' => $upstreamplanid,
                        'upstreamownerid' => $upstreamownerid,
                        'upstreamsubid' => $upstreamsubid]);

        //Get Poodll School and once its got, resume it
        $poodllschool =common::get_poodllschool_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::resume_poodllschool($poodllschool);
            $ret['message'] = 'School resumed ok';
        }else{
            $ret['error'] = true;
            $ret['message'] = 'No such school was found';
        }
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


        //initialise return value
        $ret = [];
        $ret['error'] = false;
        $ret['message'] = '';


        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::pause_sub_parameters(),
                ['upstreamplanid' => $upstreamplanid,
                        'upstreamownerid' => $upstreamownerid,
                        'upstreamsubid' => $upstreamsubid]);

        //Get Poodll School and once its got, pause it
        $poodllschool =common::get_poodllschool_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
           common::pause_poodllschool($poodllschool);
            $ret['message'] = 'School paused ok';
        }else{
            $ret['error'] = true;
            $ret['message'] = 'No such school was found';
        }
        return $ret;

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

        //initialise return value
        $ret = [];
        $ret['error'] = false;
        $ret['message'] = '';


        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::update_sub_parameters(),
                ['upstreamplanid' => $upstreamplanid,
                 'upstreamownerid' => $upstreamownerid,
                 'upstreamsubid' => $upstreamsubid]);

        //Get Poodll School and once its got, update it
        $poodllschool =common::get_poodllschool_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            if($poodllschool->upstreamplanid != $params['upstreamplanid']) {
                common::update_poodllschool_from_upstream($poodllschool->id, $params['upstreamplanid']);
                $ret['message'] = 'updated successfully';
            }else{
                $ret['message']= 'nothing to update. all good.';
            }
        }else{
            $ret['error'] = true;
            $ret['message'] = '';
        }
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

        require_once($CFG->dirroot . '/blocks/iomad_company_admin/lib.php');

        //initialise return value
        $ret=[];
        $ret['error']=false;
        $ret['message']='';
        $ret['schoolid']=0;
        $ret['userid']=0;
        $ret['username']='';

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::create_sub_parameters(),
            ['username' => $username, 'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email, 'schoolname'=>$schoolname,
                    'upstreamplanid'=>$upstreamplanid,'upstreamownerid'=>$upstreamownerid,'upstreamsubid'=>$upstreamsubid]);

        //need to massage data a bit
        $userdata= [];
        $userdata['username']=$params['username'];
        $userdata['firstname']=$params['firstname'];
        $userdata['lastname']=$params['lastname'];
        $userdata['email']=$params['email'];
        $userdata['managertype']=1;//company manager
        $userdata['educator']=1;//is an educator

        //lets add all this stuff
        //though I do not think we need to
        $userdata['city']='Tokyo';
        $userdata['country']='JP';
        $userdata['maildisplay']=2;
        $userdata['mailformat']= 1;
        $userdata['maildigest']= 0;
        $userdata['autosubscribe']=1;
        $userdata['trackforums']=0;
        $userdata['htmleditor']=1;
        $userdata['screenreader']=0;
        $userdata['timezone']= '99';
        $userdata['lang']='en';
        $userdata['suspended']= 0;
        $userdata['ecommerce']= 0;
        $userdata['parentid' ]=0;
        $userdata['customcss' ]='';
        $userdata['validto']=null;
        $userdata['suspendafter']=0;

        $companydata = $params;
        $companydata['name']=$params['schoolname'];
        $companydata['shortname']=$params['schoolname'];

        // Get/check context/capability
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('block/poodllclassroom:manageintegration', $context);


        //if we alredy have this poodllschol then we just need to update to the new plan
        //Its unclear yet if that will happen here in this call, or another one
        $poodllschool =common::get_poodllschool_by_upstreamsubid($upstreamsubid);
        if($poodllschool){
            common::update_poodllschool_from_upstream($poodllschool->id, $upstreamplanid);
            $ret['schoolid'] = $poodllschool->companyid;
            $ret['userid'] = $poodllschool->userid;
            $theuser = $DB->get_record('user',array('id'=>$poodllschool->userid));
            if($theuser) {
                $ret['username'] = $theuser->username;
            }
            return $ret;
        }


        $thecompany = common::create_company($companydata);
         if(!$thecompany){
                 $ret['error'] = true;
                 $ret['message'] = "failed to create company";
                 return $ret;
         }

         //mailout api needs this - or it does a redirect ...urg
        $SESSION->currenteditingcompany= $thecompany->id;

        $theuser = common::get_user($userdata['username'],$userdata['email']);
        $parentlevel = company::get_company_parentnode($thecompany->id);
        $departmentid=$parentlevel->id;
        $newuserid=0;
        if(!$theuser) {
            $validateddata = (object)$userdata;
            // Trim first and lastnames
            $validateddata->firstname = trim($validateddata->firstname);
            $validateddata->lastname = trim($validateddata->lastname);
            $validateddata->sendnewpasswordemails =1;
            $validateddata->due =time();
            $validateddata-> preference_auth_forcepasswordchange =1;
            $validateddata->use_email_as_username =0;
            $validateddata->userdepartment=$departmentid;

            $result = common::create_company_user($thecompany->id, $validateddata);
            if($result && $result->error==false){
                $newuserid=$result->itemid;
                $newusername=$result->username;
            }else{
                $ret['error'] = true;
                $ret['message'] = "failed to create user";
                return $ret;
            }
        }else{
            company::upsert_company_user($theuser->id, $thecompany->id, $departmentid,  $userdata['managertype'], $userdata['educator']);
            $newuserid=$theuser->id;
            $newusername=$theuser->username;
        }

        //we have created a company and possibly a user also
        //at this point we should update the poodllclassroom tables also
        $plan = common::fetch_poodllplan_from_upstreamplan($upstreamplanid);
        common::create_poodllschool($thecompany->id, $newuserid, $plan->id, $upstreamownerid,$upstreamsubid);

        if($thecompany && $newuserid) {
            $ret['schoolid'] = $thecompany->id;
            $ret['userid'] = $newuserid;
            $ret['username'] = $newusername;
        }else{
            $ret['error'] = true;
            $ret['message'] = "failed to create company AND user";
        }
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

}
