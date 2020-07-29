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
                $usernew = file_postupdate_standard_editor($usernew,
                    'description',
                    $editoroptions,
                    $usercontext,
                    'user_profile',
                    $usernew->id);
                // Trim first and lastnames
                $usernew->firstname = trim($usernew->firstname);
                $usernew->lastname = trim($usernew->lastname);
                $usernew->username = clean_param($usernew->username, PARAM_USERNAME);
                $usernew->timemodified = time();

                $DB->update_record('user', $usernew);
                // Pass a true $userold here.
            /*
                $authplugin = get_auth_plugin($usernew->auth);
                if (! $authplugin->user_update($user, $mform->get_data())) {
                    // Auth update failed, rollback for moodle.
                    $DB->update_record('user', $user);
                    print_error('cannotupdateuseronexauth', '', '', $user->auth);
                }

                // Set new password if specified.
                if (!empty($usernew->newpassword)) {
                    if ($authplugin->can_change_password()) {
                        if (!$authplugin->user_update_password($usernew, $usernew->newpassword)) {
                            print_error('cannotupdatepasswordonextauth', '', '', $usernew->auth);
                        } else {
                            EmailTemplate::send('password_update', array('user' => $usernew));
                        }
                    }
                }
                $usercreated = false;
*/

                $usercontext = context_user::instance($usernew->id);
/*
                // Update preferences.
                useredit_update_user_preference($usernew);
                if (empty($usernew->preference_auth_forcepasswordchange)) {
                    $usernew->preference_auth_forcepasswordchange = 0;
                }
                set_user_preference('auth_forcepasswordchange', $usernew->preference_auth_forcepasswordchange, $usernew->id);

                // Update tags.
                if (!empty($CFG->usetags)) {
                    useredit_update_interests($usernew, $usernew->interests);
                }

                // Update user picture.
                if (!empty($CFG->gdversion)) {
                    core_user::update_picture($usernew, array());
                }

                // Update mail bounces.
                useredit_update_bounces($user, $usernew);

                // Update forum track preference.
                useredit_update_trackforums($user, $usernew);

                // Save custom profile fields data.
                profile_save_data($usernew);

                // Reload from db.
                $usernew = $DB->get_record('user', array('id' => $usernew->id));
*/
                // Trigger events.
                \core\event\user_updated::create_from_userid($usernew->id)->trigger();

                $ret = new \stdClass();
                $ret->itemid=$userid;
                $ret->error=false;
                return json_encode($ret);
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

                    $ret = common::upsert_company_user($companyid,$validateddata,$formname);
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
