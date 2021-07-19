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



    public static function fetch_billingintervals(){
        return array(constants::M_BILLING_YEARLY=>get_string('yearly',constants::M_COMP),
                constants::M_BILLING_MONTHLY=>get_string('monthly',constants::M_COMP),
                constants::M_BILLING_FREE=>get_string('free',constants::M_COMP));
    }

    public static function fetch_planfamilies(){
        return array('CLASSROOM'=>'CLASSROOM',
                'LTI_STANDARD'=>'LTI_STANDARD',
                'M_LANG'=>'M_LANG',
                'M_MEDIA'=>'M_MEDIA',
                'M_ESSENT'=>'M_ESSENT');
    }

    public static function fetch_platforms(){
        return array(constants::M_PLATFORM_CLASSROOM=>constants::M_PLATFORM_CLASSROOM,
                constants::M_PLATFORM_LTI=>constants::M_PLATFORM_LTI,
                constants::M_PLATFORM_MOODLE=>constants::M_PLATFORM_MOODLE);
    }

    public static function fetch_plans_array(){
        global $DB;

        $plans = self::fetch_plans();
        $ret = [];
        foreach($plans as $plan){
            $ret[$plan->id]=$plan->name;
        }
        return $ret;

    }


    public static function fetch_owners_array(){
        global $DB;
        $owners = $DB->get_records('user', array());
        $ret = [];
        foreach($owners as $owner){
            $ret[$owner->id]=$owner->lastname . ' ' . $owner->firstname ;
        }
        return $ret;
    }
    public static function fetch_schools_array(){
        global $DB;

        $schools=self::fetch_schools();

        $ret = [];
        if($schools &&!empty($schools)) {
            foreach($schools as $school){
                $ret[$school->id]=$school->name;
            }
        }
        return $ret;
    }

    public static function fetch_plans(){
        global $DB;

        $plans = $DB->get_records(constants::M_TABLE_PLANS,array(),'price ASC') ;
        if($plans) {
            return $plans;
        }else{
            return [];
        }
    }

    public static function fetch_plans_by_family($planfamily){
        global $DB;

        $plans = $DB->get_records(constants::M_TABLE_PLANS,array('planfamily'=>$planfamily),'price ASC') ;
        if($plans) {
            foreach($plans as $plan){
                $plan->{'platform_' . $plan->platform} =true;
            }
            return $plans;
        }else{
            return [];
        }
    }

    public static function fetch_plans_by_platform($platform, $planfamily='all'){
        global $DB;

        $params = ['platform'=>$platform];
        $families = self::fetch_planfamilies();
        if(array_key_exists($planfamily,$families)){
            $params['planfamily'] = $planfamily;
        }
        $plans = $DB->get_records(constants::M_TABLE_PLANS,$params,'price ASC') ;
        if($plans) {
            foreach($plans as $plan){
                $plan->{'platform_' . $plan->platform} =true;
            }
            return $plans;
        }else{
            return [];
        }
    }

    public static function fetch_subs(){
        global $DB;

        $sql = 'SELECT sub.*';
        $sql .= 'from {'. constants::M_TABLE_SUBS .'} sub ';
        $subs=$DB->get_records_sql($sql);


        if($subs) {
            return $subs;
        }else{
            return [];
        }
    }

    public static function fetch_sub(){
        global $DB;

        $sql = 'SELECT sub.*';
        $sql .= 'from {'. constants::M_TABLE_SUBS .'} sub ';
        $subs=$DB->get_records_sql($sql);


        if($subs) {
            return $subs;
        }else{
            return [];
        }
    }

    public static function fetch_subs_by_user($userid){
        global $DB;

        $sql = 'SELECT sub.*, u.firstname as ownerfirstname, u.lastname as ownerlastname, school.name as schoolname ';
        $sql .= 'from {'. constants::M_TABLE_SUBS .'} sub ';
        $sql .= 'INNER JOIN {'. constants::M_TABLE_SCHOOLS .'} school ON school.id = sub.schoolid ';
        $sql .= 'INNER JOIN {user} u ON u.id = school.ownerid ';
        $sql .= 'WHERE u.id = :userid ';
        $subs=$DB->get_records_sql($sql,array('userid'=>$userid));


        if($subs) {
            return $subs;
        }else{
            return [];
        }
    }

    public static function fetch_subs_for_all_users(){
        global $DB;

        $sql = 'SELECT sub.*, u.firstname as ownerfirstname, u.lastname as ownerlastname, school.name as schoolname ';
        $sql .= 'from {'. constants::M_TABLE_SUBS .'} sub ';
        $sql .= 'INNER JOIN {'. constants::M_TABLE_SCHOOLS .'} school ON school.id = sub.schoolid ';
        $sql .= 'INNER JOIN {user} u ON u.id = school.ownerid ';
        $subs=$DB->get_records_sql($sql);


        if($subs) {
            return $subs;
        }else{
            return [];
        }
    }

    public static function fetch_subs_by_school($schoolid){
        global $DB;

        $sql = 'SELECT sub.*, u.firstname as ownerfirstname, u.lastname as ownerlastname, "fakename" as schoolname ';
        $sql .= 'FROM {'. constants::M_TABLE_SUBS .'} sub ';
        $sql .= ' INNER JOIN {user} u ON u.id = sub.ownerid ';
        $sql .= ' WHERE sub.schoolid = :schoolid ';
        $subs=$DB->get_records_sql($sql,['schoolid'=>$schoolid]);

        if($subs) {
            return $subs;
        }else{
            return [];
        }


        global $DB,$USER;
        $sql = 'SELECT sub.* ';
        $sql .= ' FROM {'. constants::M_TABLE_SUBS .'} sub ';
        $sql .= ' INNER JOIN {'. constants::M_TABLE_SCHOOLS .'} school ON school.id=sub.schoolid';
        $sql .= ' WHERE school.ownerid = :userid';
        $subs=$DB->get_records_sql($sql, array('userid'=>$USER->id));
        return $subs;

    }

    public static function fetch_schoolsubs_by_school($schoolid){
        global $DB;

        global $DB,$USER;
        $sql = 'SELECT sub.* ';
        $sql .= ' FROM {'. constants::M_TABLE_SUBS .'} sub ';
        $sql .= ' INNER JOIN {'. constants::M_TABLE_SCHOOLS .'} school ON school.id=sub.schoolid';
        $sql .= ' WHERE school.id = :schoolid';
        $subs=$DB->get_records_sql($sql, array('schoolid'=>$schoolid));

        if($subs) {
            return $subs;
        }else{
            return [];
        }

    }

    public static function fetch_subs_by_plan($planid){
        global $DB;

        $sql = 'SELECT sub.*, u.firstname as ownerfirstname, u.lastname as ownerlastname, "fakename" as schoolname ';
        $sql .= 'FROM {'. constants::M_TABLE_SUBS .'} sub ';
        $sql .= ' INNER JOIN {'. constants::M_TABLE_SCHOOLS .'} school ON school.id=sub.schoolid';
        $sql .= ' INNER JOIN {user} u ON u.id = school.ownerid ';
        $sql .= ' WHERE sub.planid = :planid ';
        $subs=$DB->get_records_sql($sql,['planid'=>$planid]);

        if($subs) {
            return $subs;
        }else{
            return [];
        }
    }

    public static function fetch_users_array(){
        global $DB,$USER;


        $users=$DB->get_records('user',null,'lastname ASC');

        $ret = [];
        if($users &&!empty($users)) {
            foreach($users as $user){
                $ret[$user->id]=$user->lastname . ' ' . $user->firstname ;
            }
        }
        return $ret;
    }

    public static function fetch_resellers(){
        global $DB;

        $sql = 'SELECT * ';
        $sql .= 'from {'. constants::M_TABLE_RESELLERS .'} reseller ';
        $resellers=$DB->get_records_sql($sql);


        if($resellers) {
            return $resellers;
        }else{
            return [];
        }
    }


    public static function fetch_resellers_array(){
        global $DB,$USER;

        global $DB;

        $resellers=self::fetch_resellers();

        $ret = [];
        if($resellers &&!empty($resellers)) {
            foreach($resellers as $reseller){
                $ret[$reseller->id]=$reseller->name;
            }
        }
        return $ret;
    }

    public static function fetch_me_reseller(){
        global $DB,$USER;

        $reseller=$DB->get_record(constants::M_TABLE_RESELLERS, ['userid'=>$USER->id]);
        return $reseller;
    }

    public static function fetch_schools_by_reseller($resellerid){
        global $DB;

        $sql = 'SELECT school.*, u.firstname as ownerfirstname, u.lastname as ownerlastname ';
        $sql .= 'from {'. constants::M_TABLE_SCHOOLS .'} school ';
        $sql .= 'INNER JOIN {user} u ON u.id = school.ownerid ';
        $sql .= 'WHERE school.resellerid = :resellerid ';
        $schools=$DB->get_records_sql($sql, ['resellerid'=>$resellerid]);


        if($schools) {
            return $schools;
        }else{
            return [];
        }
    }

    public static function fetch_schools(){
        global $DB;

        $sql = 'SELECT school.*, u.firstname as ownerfirstname, u.lastname as ownerlastname ';
        $sql .= 'from {'. constants::M_TABLE_SCHOOLS .'} school ';
        $sql .= 'INNER JOIN {user} u ON u.id = school.ownerid ';
        $schools=$DB->get_records_sql($sql);


        if($schools) {
            return $schools;
        }else{
            return [];
        }
    }

    public static function get_poodllsubs_by_currentuser(){
        global $DB,$USER;
        $sql = 'SELECT sub.* ';
        $sql .= ' FROM {'. constants::M_TABLE_SUBS .'} sub ';
        $sql .= ' INNER JOIN {'. constants::M_TABLE_SCHOOLS .'} school ON school.id=sub.schoolid';
        $sql .= ' WHERE school.ownerid = :userid';
        $subs=$DB->get_records_sql($sql, array('userid'=>$USER->id));
        return $subs;

    }

    public static function get_usersub_by_plan($planid){
        global $DB,$USER;
        $sql = 'SELECT sub.* ';
        $sql .= ' FROM {'. constants::M_TABLE_SUBS .'} sub ';
        $sql .= ' INNER JOIN {'. constants::M_TABLE_SCHOOLS .'} school ON school.id=sub.schoolid';
        $sql .= ' WHERE school.ownerid = :userid AND sub.planid = :planid ';
        $subs=$DB->get_records_sql($sql, array('userid'=>$USER->id, 'planid'=>$planid));
        return $subs;

    }

    //this will be weird in the case of a reseller who may have more than one school. check for that before getting here
    public static function get_poodllschool_by_currentuser(){
        global $DB,$USER;
        return $DB->get_record(constants::M_TABLE_SCHOOLS,array('ownerid'=>$USER->id));
    }

    public static function get_extended_sub_data($subs){
        global $DB;
        $plans= [];
        $schools=[];
        foreach($subs as $sub){
            //plans
            if(!array_key_exists($sub->planid,$plans)){
                $plans[$sub->planid] = $DB->get_record(constants::M_TABLE_PLANS,array('id'=>$sub->planid));
                $plans[$sub->planid]->extra = json_decode($plans[$sub->planid]->jsonfields);
            }
           $sub->plan = $plans[$sub->planid];
            //SCHOOLS
            if(!array_key_exists($sub->schoolid,$schools)){
                $schools[$sub->schoolid] = $DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$sub->schoolid));
                $schools[$sub->schoolid]->extra = json_decode($schools[$sub->schoolid]->jsonfields);
                if(!empty($schools[$sub->schoolid]->siteurls)) {
                    $schools[$sub->schoolid]->siteurls = json_decode($schools[$sub->schoolid]->siteurls);
                }else{
                    unset($schools[$sub->schoolid]->siteurls);
                }
                //reseller
                $schools[$sub->schoolid]->reseller =
                        $DB->get_record(constants::M_TABLE_RESELLERS,array('id'=>$schools[$sub->schoolid]->resellerid));
            }
            $sub->school = $schools[$sub->schoolid];
            //if resold, flag that
            if($schools[$sub->schoolid]->reseller->resellertype==constants::M_RESELLER_THIRDPARTY){
                $sub->resold=true;
            }

           $sub->extra=json_decode($sub->jsonfields);
        }
        return $subs;
    }

    public static function get_display_sub_data($subs){
        global $CFG;
        foreach($subs as $sub){
            //display period
            switch($sub->plan->billinginterval){
                case constants::M_BILLING_YEARLY:
                    $sub->plan->period_display=get_string('yearly',constants::M_COMP);
                    break;
                case constants::M_BILLING_MONTHLY:
                    $sub->plan->period_display=get_string('monthly',constants::M_COMP);
                    break;
                case constants::M_BILLING_FREE:
                    $sub->plan->period_display=get_string('free',constants::M_COMP);
                    break;
                default:
                    $sub->plan->period_display='';

            }
            //change plan url
            $sub->changeurl= $CFG->wwwroot . '/blocks/poodllclassroom/subs/changeplan.php?subid=' . $sub->id;
            //edit plan url
            $sub->editurl= $CFG->wwwroot . '/blocks/poodllclassroom/subs/accessportal.php?subid=' . $sub->id;

            //time created
            $sub->timecreated_display =date("Y-m-d H:i:s", $sub->timecreated);
        }
        return $subs;
    }


    public static function get_plan_by_sub($sub){
        global $DB,$USER;
        if($sub){
            $plan = $DB->get_record(constants::M_TABLE_PLANS,array('id'=>$sub->planid));
            return $plan;
        }else{
            return false;
        }
    }

    public static function get_plan($planid){
        global $DB,$USER;
            $plan = $DB->get_record(constants::M_TABLE_PLANS,array('id'=>$planid));
            return $plan;
    }

    public static function get_poodllsub_by_upstreamsubid($upstreamsubid){
        global $DB;
        return $DB->get_record(constants::M_TABLE_SUBS,array('upstreamsubid'=>$upstreamsubid));
    }

    public static function update_poodllsub_from_upstream($sub, $upstreamplanid){
        global $DB;
        $ret = false;
        $plan = self::fetch_poodllplan_from_upstreamplan($upstreamplanid);
        if($plan) {
            $ret= $DB->update_record(constants::M_TABLE_SUBS, array('id' => $sub->id, 'planid' => $plan->id, 'status'=>'active'));
            $owner = $DB->get_record('user',array('id'=>$sub->ownerid));
            if($ret && $owner && $owner->suspended){
               $ret= $DB->update_record('user', array('id' => $owner->id, 'suspended' => 0));
            }
        }
        return $ret;
    }

    public static function pause_poodllsub($sub){
        global $DB;
        $ret = self::suspend_sub($sub);
        if($ret){
            $ret = $DB->update_record(constants::M_TABLE_SUBS,
                    array('id' => $sub->id, 'status' => 'paused','timemodified'=>time()));
        }

        return $ret;
    }

    public static function resume_poodllsub($sub){
        global $DB;
        $ret = self::unsuspend_sub($sub);
        if($ret){
            $ret = $DB->update_record(constants::M_TABLE_SUBS,
                    array('id' => $sub->id, 'status' => 'active','timemodified'=>time()));
        }

        return $ret;
    }

    public static function reactivate_poodllsub($sub){
        global $DB;
        $ret = self::unsuspend_sub($sub);
        if($ret){
            $ret = $DB->update_record(constants::M_TABLE_SUBS,
                    array('id' => $sub->id, 'status' => 'active','timemodified'=>time()));
        }

        return $ret;
    }

    //TO DO what to do here?
    public static function activate_poodllsub($sub){
        global $DB;

        $ret = self::unsuspend_sub($sub);
        if($ret){
            $ret = $DB->update_record(constants::M_TABLE_SUBS,
                    array('id' => $sub->id, 'status' => 'active','timemodified'=>time()));
        }

        return $ret;
    }


    public static function cancel_poodllsub($sub){
        global $DB;

        $ret = self::suspend_sub($sub);
        if($ret){
            $ret = $DB->update_record(constants::M_TABLE_SUBS,
                    array('id' => $sub->id, 'status' => 'cancelled','timemodified'=>time()));
        }

        return $ret;
    }

    public static function suspend_sub($sub){
        global $USER;


        return true;
    }

    public static function unsuspend_sub($sub){
        global $USER;

        return true;
    }

    //if we have a sale we need to keep the data and deal with any fallout if the plan had no matching pclassroom equiv.
    public static function fetch_poodllplan_from_upstreamplan($upstreamplanid){
        global $DB;
        $plan = $DB->get_record(constants::M_TABLE_PLANS, array('upstreamplan'=>$upstreamplanid));
        if(!$plan){
           $plan = self::create_blankplan($upstreamplanid);
        }
        return $plan;
    }

    public static function create_poodllsub($schoolid, $ownerid, $planid, $upstreamownerid,$upstreamsubid, $jsonfields='{}'){
        global $DB;
        $newschool= new \stdClass();
        $newschool->schoolid=$schoolid;
        $newschool->ownerid=$ownerid;
        $newschool->planid=$planid;
        $newschool->upstreamownerid=$upstreamownerid;
        $newschool->upstreamsubid=$upstreamsubid;
        $newschool->status='active';
        $newschool->jsonfields=$jsonfields;
        $newschool->timecreated=time();
        $newschool->timemodified=time();

        $subid = $DB->insert_record(constants::M_TABLE_SUBS,$newschool);
        return $subid;
    }

    public static function create_blankplan($upstreamplanid){
        global $DB;
        $newplan= new \stdClass();
        $newplan->name=$upstreamplanid;
        $newplan->maxusers=10;
        $newplan->maxcourses=10;
        $newplan->timemodified=time();
        $newplan->id= $DB->insert_record(constants::M_TABLE_PLANS,$newplan);
        return $newplan;
    }

    public static function get_my_schoolid(){
        global $DB;

        return 1;
    }

    public static function get_school_by_sub($sub){
        global $DB;
        $school = $DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$sub->schoolid));
        if($school) {
            return $school;
        }else{
            return false;
        }

    }

    public static function get_schoolname_by_sub($sub){
        global $DB;
        $school = $DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$sub->schoolid));
        if($school) {
            return $school->name;
        }else{
            return 'no school(bad)';
        }

    }

    public static function set_schoolname_by_sub($sub,$schoolname){
        global $DB;
        $ret = true;
        return $ret;

    }
    public static function set_schoolname($school,$name){
        global $DB;
        $school->name=$name;
        $school->timemodified =time();
        $ret = $DB->update_record(constants::M_TABLE_SCHOOLS,$school);
        return $ret;

    }

    public static function get_resold_or_my_school($schoolid=0){
        global $DB;
        //generally speaking the reseller is Poodll(=1),
        $reseller = common::fetch_me_reseller();
        $school=false;
        if($reseller && $reseller->resellertype == constants::M_RESELLER_THIRDPARTY) {
            $schools = common::fetch_schools_by_reseller($reseller->id);
            foreach ($schools as $aschool){
                if($aschool->id==$schoolid){
                    $school = $aschool;
                }
            }
        }elseif($reseller && $reseller->resellertype == constants::M_RESELLER_POODLL){
            $school = $DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$schoolid));

        }else{
            $school=common::get_poodllschool_by_currentuser();
            if(!$school || $school->id != $schoolid){
                $school=false;
            }
        }
        return $school;
    }

    public static function curl_fetch($url, $postdata = false, $username='') {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();


        if(!empty($username)) {
            $curl->setopt(array('CURLOPT_HTTPAUTH'=>CURLAUTH_BASIC));
            $curl->setopt(array('CURLOPT_USERPWD'=> $username . ":"));
        }

        if($postdata) {
            $postdatastring = http_build_query($postdata, '', '&');
            $result = $curl->post($url, $postdatastring);
        }else{
            $result = $curl->get($url);
        }
        return $result;
    }

    //see if this is truly json or some error
    public static function make_object_from_json($string) {
        if (!$string) {
            return false;
        }
        if (empty($string)) {
            return false;
        }
        $object = json_decode($string);
        if(json_last_error() == JSON_ERROR_NONE){
            return $object;
        }else{
            return false;
        }
    }

    public static function fetch_integration_options(){
        return array(constants::M_INTEGRATION_POODLLNET=>'Poodll NET',
                constants::M_INTEGRATION_CLOUDPOODLL=>'Poodll CLOUD');
    }

    public static function get_portalurl_by_upstreamid($upstreamid){
        global $CFG;

        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        if($upstreamid && !empty($apikey) && !empty($siteprefix)){
            $url = "https://$siteprefix.chargebee.com/api/v2/portal_sessions";
            $postdata=[];
            $postdata['redirect_url'] = $CFG->wwwroot . '/my';
            $postdata['customer']= array("id" => $upstreamid);
            $curlresult = self::curl_fetch($url,$postdata,$apikey);
            $jsonresult = self::make_object_from_json($curlresult);
            if($jsonresult){
                if(isset($jsonresult->portal_session->access_url)) {
                    $portalurl = $jsonresult->portal_session->access_url;
                    if ($portalurl && !empty($portalurl)) {
                        return $portalurl;
                    }
                }else{
                   //this causes infinite redirect ...
                    // redirect($postdata['redirect_url'],get_string('noaccessportal',constants::M_COMP));
                    return '';
                }
            }
        }
        return false;
    }

    public static function get_portalurl_by_sub($sub){
        global $CFG;
        //poodll reseller = client bought direct
        if($sub->school->reseller->resellertype==constants::M_RESELLER_POODLL) {
            $customerid = $sub->school->upstreamownerid;
        //otherwise its a 3rd party reseller
        }else{
            $customerid = $sub->school->reseller->upstreamuserid;
        }
        return self::get_portalurl_by_upstreamid($customerid);
    }

    public static function get_checkout_existing($planid){
        global $USER, $CFG;
        $sub = self::get_usersub_by_plan($planid);
        $extended_sub = self::get_extended_sub_data([$sub])[0];
        $schoolname=$extended_sub->school->name;
        $customerid = $sub->upstreamownerid;
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        if($customerid && !empty($apikey) && !empty($siteprefix)){
            $url = "https://$siteprefix.chargebee.com/api/v2/hosted_pages/checkout_existing";
            $postdata=[];
            $postdata['redirect_url'] = $CFG->wwwroot . constants::M_URL . '/subs/welcomeback.php';
            $postdata['cancel_url'] = $CFG->wwwroot . '/my';
            $postdata['subscription']= array(
                    "id" => $sub->upstreamsubid,
                    "plan_id" => $sub->plan->upstreamplan,
                    "cf_school_name"=>$schoolname,
                    );
            $curlresult = self::curl_fetch($url,$postdata,$apikey);
            $jsonresult = self::make_object_from_json($curlresult);
            if($jsonresult){
                return $jsonresult;
            }
        }
        return false;
    }

    public static function retrieve_hosted_page($id){
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        $url = "https://$siteprefix.chargebee.com/api/v2/hosted_pages/";
        $url .= $id;

        $postdata=false;
        $curlresult = self::curl_fetch($url,$postdata,$apikey);
        $jsonresult = self::make_object_from_json($curlresult);
        if($jsonresult){
            return $jsonresult;
        }
        return false;
    }


    public static function make_upstream_user_id($userid)
    {
        return 'user-'.$userid.'-'.random_string(8);

    }
    public static function create_blank_school(){
        global $USER, $DB;
        $school = new \stdClass();
        $school->name='unnamed school';
        $school->ownerid = $USER->id;
        $school->resellerid = constants::M_RESELLER_POODLL;
        $school->upstreamownerid = self::make_upstream_user_id($USER->id);
        $id = $DB->insert_record(constants::M_TABLE_SCHOOLS,$school);
        if($id){
            $school->id = $id;
            return $school;
        }else{
            return false;
        }

    }

    public static function get_checkout_new($planid, $currency, $billinginterval, $schoolid=0){
        global $USER, $CFG;
        $plan = self::get_plan($planid);
        switch($billinginterval){
            case constants::M_BILLING_MONTHLY:
                $billing='Monthly';
                break;
            case constants::M_BILLING_YEARLY:
            default:
                $billing='Yearly';
                break;

        }
        if(!$plan){
            return false;
        }
        $reseller = self::fetch_me_reseller();
        $school = self::get_resold_or_my_school($schoolid);
        if($reseller){
            if(!$school){return false;}
            $upstreamuserid=$reseller->upstreamuserid;
        }elseif ($school){
            $upstreamuserid=$school->upstreamownerid;
        }else{
            //in this case we dont gots no school nor gots us no upstreamuserid
            //create a school and a random upstreamid
            $school=self::get_poodllschool_by_currentuser();
            if(!$school){
               $school = self::create_blank_school();
            }
            if($school){
                $upstreamuserid=$school->upstreamownerid;
            }else{
                return false;
            }
        }

        $schoolname=$school->name;
        $customerid = $upstreamuserid;
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');


        if($customerid && !empty($apikey) && !empty($siteprefix)){
            //$url = "https://$siteprefix.chargebee.com/api/v2/hosted_pages/checkout_new";
            $url = "https://$siteprefix.chargebee.com/api/v2/hosted_pages/checkout_new_for_items";

            $postdata=[];
            $postdata['redirect_url'] = $CFG->wwwroot . constants::M_URL . '/subs/welcomeback.php';
            $postdata['cancel_url'] = $CFG->wwwroot . '/my';
            $postdata['subscription_items']=[];
            $postdata['subscription_items']['item_price_id']=[];
            $postdata['subscription_items']['quantity']=[];

            $postdata['subscription_items']['item_price_id'][0] = $plan->upstreamplan . '-' .  $currency . '-'  . $billing;
            $postdata['subscription_items']['quantity'][0]=1;
/*
            $postdata['subscription_items'][0]= array(
                "plan_id" =>
                "cf_school_name"=>$schoolname,
            );
*/
            $postdata['customer']= array(
                "id" => $upstreamuserid,
                "email" => $USER->email,
                "first_name" => $USER->firstname,
                "last_name" => $USER->lastname,
            );
            if($reseller){
                $postdata['company'] = $reseller->name;
            }else{
                $postdata['company'] = $schoolname;
            }

            //allow offline payment
            $postdata['allow_offline_payment_methods'] = true;

            //passthrough
            $passthrough = [];
            $passthrough['schoolid']=$school->id;
            $passthrough['planid']=$plan->id;
            $passthrough['currency']=$currency;
            $passthrough['billing']=$billing;
            $postdata['pass_thru_content'] = json_encode($passthrough);


            $curlresult = self::curl_fetch($url,$postdata,$apikey);
            $jsonresult = self::make_object_from_json($curlresult);
            if($jsonresult){
                return $jsonresult;
            }
        }
        return false;
    }
}//end of class
