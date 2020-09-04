<?php
// This file is part of Moodle - http://moodle.org/
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

namespace block_poodllclassroom;

use block_poodllclassroom\constants;

defined('MOODLE_INTERNAL') || die();


/**
 *
 * This is a class containing constants and static functions for general use around the plugin
 *
 * @package   block_poodllclassroom
 * @since      Moodle 3.8
 * @copyright  2020 Justin Hunt (https://poodll,com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
class common
{
    //This function is called from the dosomething scheduled task and from the button on the view page
    //the "something" is merely to raise the something_happened event
    public static function do_something($blockid=0)
	{
        $eventdata = self::fetch_event_data($blockid);
        $event = \block_poodllclassroom\event\something_happened::create($eventdata);
        $event->trigger();
	}

    //this is a helper function to prepare data to be passed to the something_happened event
	public static function fetch_event_data($blockid=0){
        global $USER;
        $config = self::fetch_best_config($blockid);

        if($blockid==0) {
            $eventdata = array(
                'context' => \context_system::instance(0),
            'userid' => 0,
            'relateduserid' => 0,
            'other' => $config->sometext
            );
        }else{

            $eventdata = array(
                'context' => \context_block::instance($blockid),
            'userid' => $USER->id,
            'relateduserid'=> 0,
            'other' => $config->sometext
            );
        }
		return $eventdata;
    }

    //this merges the local config and admin config settings to make it easy to assume there is a setting
    //and to get it.
    public static function fetch_best_config($blockid=0){
	    global $DB;

        $config = get_config(constants::M_COMP);
        $local_config = false;
        if($blockid > 0) {
            $configdata = $DB->get_field('block_instances', 'configdata', array('id' => $blockid));
            if($configdata){
                $local_config = unserialize(base64_decode($configdata));
            }

            if($local_config){
                $localvars = get_object_vars($local_config);
                foreach($localvars as $prop=>$value){
                    $config->{$prop}=$value;
                }
            }
        }
        return $config;
    }

    public static function send_maxusersreached_message(){
        global $DB, $CFG;

            $adminemail = get_config('core','supportemail');
            $recipient = $DB->get_record('user',array('email'=>$adminemail));
            if(!$recipient){
                return false;
            }

            // Add information about the recipient and site to $a.
            $a=new \stdClass();
            $a->username     = fullname($recipient);
            $a->maximumusers = get_config(constants::M_COMP,'maximumusers');
            $a->sitename = get_config('core','fullname');;

            // Prepare the message.
            $eventdata = new \core\message\message();
            $eventdata->courseid          = 1;
            $eventdata->component         = constants::M_COMP;
            $eventdata->name              = 'maxusersreached';
            $eventdata->notification      = 1;

            $eventdata->userfrom          = \core_user::get_noreply_user();
            $eventdata->userto            = $recipient;
            $eventdata->subject           = get_string('maxusersreachedsubject', constants::M_COMP, $a);
            $eventdata->fullmessage       = get_string('maxusersreachedbody', constants::M_COMP, $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';

            $eventdata->smallmessage      = get_string('maxusersreachedsmall', constants::M_COMP, $a);
            $eventdata->contexturl        = $CFG->wwwroot;
            $eventdata->contexturlname    = 'home';

            // ... and send it.
            return message_send($eventdata);

    }

    //This checks if can register new users
    public static function can_create_users() {
        global $DB;
        if(empty(get_config('core','registerauth')) ||
                !get_config('core','authpreventaccountcreation')){
            return false;
        }
        return true;
    }

    //This checks if can add new courses
    public static function can_create_courses() {
        global $DB;

        return true;
    }

    //This disables user registrations
    public static function disable_user_creations() {
        global $DB;
        set_config('registerauth','');
        set_config('authpreventaccountcreation',true);
        self::alter_role_byname('pnteacher','moodle/user:create','prevent');
        self::send_maxusersreached_message();

    }
    //This disables course registrations
    public static function enable_user_creations() {
        global $DB;
        set_config('registerauth','enrolkey');
        set_config('authpreventaccountcreation',false);
        self::alter_role_byname('pnteacher','moodle/user:create','allow');

    }
    //This disables course registrations
    public static function disable_course_creations() {
        global $DB;

    }
    //This enables course registrations
    public static function enable_course_creations() {
        global $DB;

    }
    //This gets active user count
    public static function fetch_active_user_count() {
        global $DB;
        $totalactiveusers = $DB->count_records('user', array('deleted' => 0,'suspended' => 0));
        return $totalactiveusers;

    }
    //This gets active course count
    public static function fetch_active_course_count() {
        global $DB;

        $totalcourses = $DB->count_records('course') - 1;
        return $totalcourses;

    }

    //Alter the role with the capability
    //$action =
    public static function alter_role_byname($rolename,$capabilityname, $action='allow' )
    {
        global $CFG, $DB;

        require_once($CFG->libdir . DIRECTORY_SEPARATOR . "accesslib.php");
        $systemcontext=1;

        //Get role by name
        $role = $DB->get_record('role', array('shortname' => $rolename));
        if (!$role) {
            return false;
        }


        $capability = CAP_ALLOW;
        switch ($action) {
            case 'inherit': $capability = CAP_INHERIT; break;
            case 'allow': $capability = CAP_ALLOW; break;
            case 'prevent': $capability = CAP_PREVENT; break;
            case 'prohibit': $capability = CAP_PROHIBIT; break;
        }
        if (assign_capability($capabilityname,$capability,$role->id,$systemcontext,true)) {
            //echo "Capability $capabilityname was set to {$capability} for roleid {$role->id} ({$role->shortname}) successfuly\n";
        }
        return true;
    }



    public static function fetch_settings_url($setting, $courseid=1){
        global $CFG;

        //type specific settings
        switch($setting) {
            case constants::SETTING_MANAGEUSERS:
                return new \moodle_url($CFG->wwwroot . '/admin/user.php', array());
            case constants::SETTING_ADDCOURSE:
                return new \moodle_url($CFG->wwwroot . '/local/simple_course_creator/view.php',
                        array('category'=>1));
            case constants::SETTING_MANAGECOURSES:
                return new \moodle_url($CFG->wwwroot . '/course/management.php?categoryid=1&view=courses',
                        array('categoryid'=>1,'view'=>'courses'));
            case constants::SETTING_MICROSOFTAUTH:
            case constants::SETTING_FACEBOOKAUTH:
            case constants::SETTING_GOOGLEAUTH:
            case constants::SETTING_WEBHOOKSFORM:
            case constants::SETTING_ENROLKEYFORM:
            case constants::SETTING_SITEDETAILSFORM:

                return new \moodle_url(constants::M_URL . '/siteadmin.php',
                        array('courseid'=>$courseid,'type'=>$setting));

            case constants::SETTING_NONE:
            default:
        }
    }

    public static function fetch_settings_title($setting){
        //type specific settings
        switch($setting) {
            case constants::SETTING_MICROSOFTAUTH:
                return get_string('microsoftauth_title',constants::M_COMP);
            case constants::SETTING_FACEBOOKAUTH:
                return get_string('facebookauth_title',constants::M_COMP);
            case constants::SETTING_GOOGLEAUTH:
                return get_string('googleauth_title',constants::M_COMP);
            case constants::SETTING_WEBHOOKSFORM:
                return get_string('webhook_title',constants::M_COMP);
            case constants::SETTING_ENROLKEYFORM:
                return get_string('enrolkeyform_title',constants::M_COMP);
            case constants::SETTING_SITEDETAILSFORM:
                return get_string('sitedetailsform_title',constants::M_COMP);
            case constants::SETTING_MANAGEUSERS:
                return get_string('manageusers_title',constants::M_COMP);
            case constants::SETTING_MANAGECOURSES:
                return get_string('managecourses_title',constants::M_COMP);
            case constants::SETTING_ADDCOURSE:
                return get_string('addcourse_title',constants::M_COMP);
            case constants::SETTING_NONE:
            default:
        }
    }

    //truncate long names that go over db limit
    public static function truncate_string($string, $length, $dots = "...") {
        return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
    }

    //expects an array of data with some fields like name /city/country
    public static function create_company($company){
        global $DB;

        // does this company already exist, if so suffix our way to an optimal name
        $newname=$company['name'];
        if ($DB->get_record('company', array('name' =>  $newname))) {
            for($suffixcount=1;$suffixcount<21;$suffixcount++) {
                $newname = $company['name'] . '_' . $suffixcount;
                if (!$DB->get_record('company', array('name' => $newname))) {
                    $company['name'] = $newname;
                    $company['shortname']= self::truncate_string($newname,25);
                    break;
                }
                //give up at 20
                if($suffixcount==20) {
                    throw new \invalid_parameter_exception('Company name is already being used');
                }
            }
        }
        $newshortname=$company['shortname'];
        if ($DB->get_record('company', array('shortname' =>  $newshortname))) {
            for($suffixcount=1;$suffixcount<21;$suffixcount++) {
                $newshortname =self::truncate_string($company['shortname'],22)  . '_' . $suffixcount;
                if (!$DB->get_record('company', array('shortname' => $newshortname))) {
                    $company['shortname']=$newshortname;
                    break;
                }
                //give up at 20
                if($suffixcount==20) {
                    throw new \invalid_parameter_exception('Company short name is already being used');
                }
            }
        }

        // Create the company record
        $companyid = $DB->insert_record('company', $company);

        // Deal with certificate info.
        $certificateinforec = array('companyid' => $companyid,
            'uselogo' => 1,
            'usesignature' => 1,
            'useborder' => 1,
            'usewatermark' => 1,
            'showgrade' => 1);
        $DB->insert_record('companycertificate', $certificateinforec);

        // Fire an event for this.
        $eventother = array('companyid' => $companyid);
        $event = \block_iomad_company_admin\event\company_created::create(array('context' =>\context_system::instance(),
            'userid' => '-1',
            'objectid' => $companyid,
            'other' => $eventother));
        $event->trigger();

        // Set up default department.
        \company::initialise_departments($companyid);


        // Set up course category for company.
        $coursecat = new \stdclass();
        $coursecat->name = $company['name'];
        $coursecat->sortorder = 999;
        $coursecat->id = $DB->insert_record('course_categories', $coursecat);
        $coursecat->context = \context_coursecat::instance($coursecat->id);
        $categorycontext = $coursecat->context;
        $categorycontext->mark_dirty();
        $DB->update_record('course_categories', $coursecat);
        fix_course_sortorder();
        $companydetails = $DB->get_record('company', array('id' => $companyid));
        $companydetails->category = $coursecat->id;
        $DB->update_record('company', $companydetails);

        return $companydetails;

    }

    public static function fetch_company_users($companyid){
        global $CFG,$DB;

        $users = $DB->get_records_sql('SELECT u.* FROM {user} u INNER JOIN {company_users} cu ON cu.userid = u.id WHERE cu.companyid=:companyid', array('companyid' => $companyid)) ;
        return $users;
    }

    public static function fetch_company_courses($companyid){
        global $CFG,$DB;


        $params = array();
        $params['companyid'] = $companyid;
        $companysql = " (c.id IN (
                          SELECT courseid FROM {company_course}
                          WHERE companyid = :companyid)
                         OR ic.shared = 1) ";



        // Set up the SQL for the table.
        $selectsql = " ic.id, c.id AS courseid, c.fullname AS coursename, ic.licensed, ic.shared, ic.validlength, ic.warnexpire, ic.warncompletion, ic.notifyperiod, ic.expireafter, ic.warnnotstarted, ic.hasgrade, '$companyid' AS companyid";
        $fromsql = " FROM {iomad_courses} ic JOIN {course} c ON (ic.courseid = c.id)";
        $wheresql = " WHERE $companysql ";
        $sqlparams = $params;


        $companies = $DB->get_records_sql('SELECT '. $selectsql . $fromsql . $wheresql, $sqlparams) ;
        return $companies;
    }

    public static function get_my_companyid($context){
        //iomad expects admins to have a selected companyid from admin block
        //so if this is called too soon from poodll_classroom will too-many-redirects you (race condition)
        //$required = false avoids this, but life will be hard till you have a company selected
        //so we return 1
        $required = false;
        $companyid = \iomad::get_my_companyid($context, $required);
        if(!$companyid && \iomad::has_capability('block/iomad_company_admin:edit_departments', $context)){
            $companyid = 1;
        }
        return $companyid;
    }


    public static function remove_course_from_company($context,$companyid,$courseid){
        global $DB;

        $company = new \company($companyid);
        $oktounenroll=true;
        $removecourse = self::fetch_company_course($companyid,$courseid);

            // Check if its a shared course.
            if ($DB->get_record_sql("SELECT id FROM {iomad_courses}
                                             WHERE courseid=:removecourse
                                             AND shared != 0",
                array('removecourse' => $removecourse->id))) {
                $DB->delete_records('company_shared_courses',
                    array('companyid' => $company->id,
                        'courseid' => $removecourse->id));
                $DB->delete_records('company_course',
                    array('companyid' => $company->id,
                        'courseid' => $removecourse->id));
                \company::delete_company_course_group($company->id,
                    $removecourse,
                    $oktounenroll);
            } else {
                // If company has enrollment then we must have BOTH
                // oktounenroll true and the company_course_unenrol capability.
                if (!empty($removecourse->has_enrollments)) {
                    if (\iomad::has_capability('block/iomad_company_admin:company_course_unenrol',
                            $context) and $oktounenroll) {
                        self::unenroll_all($removecourse->id);

                            // Remove it from the company.
                            $company->remove_course($removecourse, $company->id);
                    }
                } else {
                        $company->remove_course($removecourse, $company->id);
                }
            }
            return true;
    }

    public static function remove_user_from_company($context, $company,$userid){
        global $DB, $USER;
        $companyid=$company->id;
        $systemcontext = \context_system::instance();
        if (!\iomad::has_capability('block/iomad_company_admin:editusers', $systemcontext)) {
            print_error('nopermissions', 'error', '', 'delete a user');
        }

        if (!$user = $DB->get_record('user', array('id' => $userid))) {
            print_error('nousers', 'error');
        }

        if (!\company::check_canedit_user($companyid, $user->id)) {
            print_error('invaliduserid');
        }

        if (is_primary_admin($user->id)) {
            print_error('nopermissions', 'error', '', 'delete the primary admin user');
        }

         // Actually delete the user.
         \company_user::delete($user->id);

        // Create an event for this.
        $eventother = array('userid' => $user->id, 'companyname' => $company->get_name(), 'companyid' => $companyid);
        $event = \block_iomad_company_admin\event\company_user_deleted::create(array('context' => \context_system::instance(),
            'objectid' => $user->id,
            'userid' => $USER->id,
            'other' => $eventother));
        $event->trigger();
       return true;


    }

    public static function unenroll_all($id) {
        global $DB, $PAGE;
        // Unenroll everybody from given course.
        // Get list of enrollments.
        $course = $DB->get_record('course', array('id' => $id));
        $courseenrolment = new \course_enrolment_manager($PAGE, $course);
        $userlist = $courseenrolment->get_users('', 'ASC', 0, 0);
        foreach ($userlist as $user) {
            $ues = $courseenrolment->get_user_enrolments($user->id);
            foreach ($ues as $ue) {
                $courseenrolment->unenrol_user($ue);
            }
        }
    }

    public static function update_company_user($companyid,$usernew,$user, $editoroptions)
    {
        global $CFG,$DB;

        $company = new \company($companyid);
        $systemcontext = \context_system::instance();

        // Trim first and lastnames
        $usernew->firstname = trim($usernew->firstname);
        $usernew->lastname = trim($usernew->lastname);
        $usercontext = \context_user::instance($usernew->id);

        if (empty($usernew->auth)) {
            // User editing self.
            $authplugin = get_auth_plugin($user->auth);
            unset($usernew->auth); // Can not change/remove.
        } else {
            $authplugin = get_auth_plugin($usernew->auth);
        }

        $usernew->username = clean_param($usernew->username, PARAM_USERNAME);
        $usernew->timemodified = time();
/* not actually showing any description or profile pic areas
        $usernew = file_postupdate_standard_editor($usernew,
            'description',
            $editoroptions,
            $usercontext,
            'user_profile',
            $usernew->id);
*/
        $DB->update_record('user', $usernew);
        // Pass a true $userold here.
        if (! $authplugin->user_update($user, $usernew)) {
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
                    \EmailTemplate::send('password_update', array('user' => $usernew));
                }
            }
        }
        $usercreated = false;

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
        // we do not do that from here
        /*
        if (!empty($CFG->gdversion)) {
            \core_user::update_picture($usernew, array());
        }
         */
        // Update mail bounces.
        useredit_update_bounces($user, $usernew);

        // Update forum track preference.
        //useredit_update_trackforums($user, $usernew);

        // Save custom profile fields data.
        profile_save_data($usernew);

        // Reload from db.
        $usernew = $DB->get_record('user', array('id' => $usernew->id));

        // Trigger events.
        \core\event\user_updated::create_from_userid($usernew->id)->trigger();

        $ret = new \stdClass();
        $ret->itemid=$usernew->id;
        $ret->message='';
        $ret->error=false;
        return $ret;

    }

    public static function get_user($username,$email)
    {
        global $DB;
       $theuser = $DB->get_record('user', array('username' => $username));
       if(!$theuser){
           $theuser = $DB->get_record('user', array('email' => $email));
       }
       if(!$theuser){
           return false;
       }else{
           return $theuser;
       }


    }
    public static function create_company_user($companyid,$validateddata)
    {
        global $CFG, $DB, $USER;

        $validateddata->userid = $USER->id;
        if ($companyid > 0) {
            $validateddata->companyid = $companyid;
        }
        $company = new \company($companyid);
//error_log(print_r( $validateddata, true ));

       /* sendnewpasswordemails
       due
       preference_auth_forcepasswordchange
       newpassword
       use_email_as_username
       */
        if (!$userid = \company_user::create($validateddata)) {
            $ret = new \stdClass();
            $ret->itemid=0;
            $ret->message='Error creating the user';
            $ret->error=true;
            return $ret;
        }
        $user = new \stdclass();
        $user->id = $userid;
        $validateddata->id = $userid;

        // Save custom profile fields data.
        profile_save_data($validateddata);

        $systemcontext = \context_system::instance();

        // Check if we are assigning a different role to the user.
        if (!empty($validateddata->managertype || !empty($validateddata->educator))) {
            \company::upsert_company_user($userid, $companyid, $validateddata->userdepartment, $validateddata->managertype,
                $validateddata->educator);
        }

        // Assign the user to the default company department.
        $parentnode = \company::get_company_parentnode($companyid);
        if (\iomad::has_capability('block/iomad_company_admin:edit_all_departments', $systemcontext)) {
            $userhierarchylevel = $parentnode->id;
        } else {
            $userlevel = $company->get_userlevel($USER);
            $userhierarchylevel = $userlevel->id;
        }
        \company::assign_user_to_department($validateddata->userdepartment, $userid);

        //get user data
        $userdata = $DB->get_record('user', array('id' => $userid));

        // Enrol the user on the courses.
        $createcourses=[];//we might want to poppulate this ...
        if (!empty($createcourses)) {
            \company_user::enrol($userdata, $createcourses, $companyid);
        }
        // Assign and licenses.
        $licenseid='';
        if (!empty($licenseid)) {
            $licenserecord = (array) $DB->get_record('companylicense', array('id' => $licenseid));
            if (!empty($licenserecord['program'])) {
                // If so the courses are not passed automatically.
                $validateddata->licensecourses = $DB->get_records_sql_menu("SELECT c.id, clc.courseid FROM {companylicense_courses} clc
                                                                   JOIN {course} c ON (clc.courseid = c.id
                                                                   AND clc.licenseid = :licenseid)",
                    array('licenseid' => $licenserecord['id']));
            }

            if (false && !empty($validateddata->licensecourses)) {
                $userdata = $DB->get_record('user', array('id' => $userid));
                $count = $licenserecord['used'];
                $numberoflicenses = $licenserecord['allocation'];
                foreach ($validateddata->licensecourses as $licensecourse) {
                    if ($count >= $numberoflicenses) {
                        // Set the used amount.
                        $licenserecord['used'] = $count;
                        $DB->update_record('companylicense', $licenserecord);
                        redirect(new \moodle_url("/blocks/iomad_company_admin/company_license_users_form.php",
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
                        \block_iomad_company_admin\event\user_license_assigned::create(array('context' => \context_course::instance($licensecourse),
                            'objectid' => $licenseid,
                            'courseid' => $licensecourse,
                            'userid' => $userdata->id,
                            'other' => $eventother));
                    $event->trigger();
                    $count++;
                }
            }
        }

        $ret = new \stdClass();
        $ret->itemid=$userid;
        $ret->username=$userdata->username;
        $ret->message='';
        $ret->error=false;
        return $ret;
    }

    public static function upsert_company_course($companyid,$validateddata,$editoroptions, $formtype)
    {
        global $CFG, $DB, $USER;


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

        //if we are editing an existing course thats easy
        if($formtype == constants::FORM_EDITCOURSE){
            update_course($mergeddata, $editoroptions);
                $ret = new \stdClass();
                $ret->itemid=
                $mergeddata->id;
                $ret->message='';
                $ret->error=false;
                return $ret;
        }

        //if its a new course, we have some work to do
        if (!$course = create_course($mergeddata, $editoroptions)) {
            $ret = new \stdClass();
            $ret->itemid=0;
            $ret->message='Error creating course';
            $ret->error=true;
            return $ret;
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
        $company = new \company($companyid);
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

        $ret = new \stdClass();
        $ret->itemid=$course->id;
        $ret->message='';
        $ret->error=false;
        return $ret;
    }


    public static function fetch_company_course($companyid,$courseid){
        global $CFG,$DB;


        $sqlparams = array();
        //$sqlparams['companyid'] = $companyid;
        $sqlparams['courseid'] = $courseid;
        $sqlwhere = " c.id= :courseid";
       // $sqlwhere = " ic.id = :companyid AND c.id= :courseid";



        // Set up the SQL for the table.
        $selectsql = "c.*";
        $fromsql = " FROM {iomad_courses} ic INNER JOIN {course} c ON (ic.courseid = c.id)";
        $wheresql = " WHERE $sqlwhere ";

        $thesql = 'SELECT '. $selectsql . $fromsql . $wheresql;
        $thecourse = $DB->get_record_sql($thesql, $sqlparams) ;
        return $thecourse;
    }

}//end of class
