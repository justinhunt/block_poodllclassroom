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

    public static function fetch_extended_sub($subid){
        global $DB;
        $sub = $DB->get_record(constants::M_TABLE_SUBS,array('id'=>$subid));
        $ext_subs = self::get_extended_sub_data([$sub]);
        return array_shift($ext_subs);
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

        $sql = 'SELECT sub.*, u.firstname as ownerfirstname, u.lastname as ownerlastname, school.name as schoolname ';
        $sql .= 'FROM {'. constants::M_TABLE_SUBS .'} sub ';
        $sql .= ' INNER JOIN {'. constants::M_TABLE_SCHOOLS .'} school ON school.id = sub.schoolid ';
        $sql .= ' INNER JOIN {user} u ON u.id = school.ownerid ';
        $sql .= ' WHERE sub.schoolid = :schoolid ';
        $subs=$DB->get_records_sql($sql,['schoolid'=>$schoolid]);

        if($subs) {
            return $subs;
        }else{
            return [];
        }
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

        $sql = 'SELECT reseller.* , u.firstname as resellerfirstname, u.lastname as resellerlastname ';
        $sql .= 'from {'. constants::M_TABLE_RESELLERS .'} reseller ';
        $sql .= 'INNER JOIN {user} u ON u.id = reseller.userid ';
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

    public static function fetch_me_reseller($userid=false){
        global $DB,$USER;
        if($userid==false){$userid=$USER->id;}
        $reseller=$DB->get_record(constants::M_TABLE_RESELLERS, ['userid'=>$userid]);
        return $reseller;
    }


    public static function fetch_school_and_owner($schoolid){
        global $DB;

        $sql = 'SELECT school.*, u.firstname as ownerfirstname, u.lastname as ownerlastname ';
        $sql .= 'from {'. constants::M_TABLE_SCHOOLS .'} school ';
        $sql .= 'INNER JOIN {user} u ON u.id = school.ownerid ';
        $sql .= 'WHERE school.id = :id ';
        $schools=$DB->get_records_sql($sql, ['id'=>$schoolid]);


        if($schools && count($schools)>0) {
            return array_shift($schools);
        }else{
            return false;
        }
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

    public static function get_usersub_by_plan($planid, $schoolid){
        global $DB,$USER;
        $sql = 'SELECT sub.* ';
        $sql .= ' FROM {'. constants::M_TABLE_SUBS .'} sub ';
        $sql .= ' INNER JOIN {'. constants::M_TABLE_SCHOOLS .'} school ON school.id=sub.schoolid';
        $sql .= ' WHERE school.ownerid = :userid AND sub.planid = :planid AND school.id = :schoolid AND NOT sub.status = \'inactive\'';
        $subs=$DB->get_records_sql($sql, array('userid'=>$USER->id, 'planid'=>$planid, 'schoolid'=>$schoolid));
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
            $sub->changeurl= $CFG->wwwroot . '/blocks/poodllclassroom/subs/changesubscription.php?subid=' . $sub->id;
            //edit plan url
            $sub->editurl= $CFG->wwwroot . '/blocks/poodllclassroom/subs/accessportal.php?subid=' . $sub->id;

            //payment
            $amount = $sub->payment;
            if($sub->paymentcurr!=='JPY'){
                $amount = floatval($sub->payment) / 100;
            }
            $fmt = \numfmt_create( 'en_US', \NumberFormatter::CURRENCY );
            $sub->payment_display =  \numfmt_format_currency($fmt, $amount, $sub->paymentcurr)."\n";

            //status
            switch($sub->status){
                case constants::M_STATUS_PAYMENTDUE:
                    $sub->status_display=get_string('paymentdue',constants::M_COMP);
                    break;
                case constants::M_STATUS_INACTIVE:
                    $sub->status_display=get_string('inactive',constants::M_COMP);
                    break;
                case constants::M_STATUS_ACTIVE:
                    $sub->status_display=get_string('active',constants::M_COMP);
                    break;
                case constants::M_STATUS_NONE:
                default:
                    $sub->status_display='-';
            }

            //expiry date
            $sub->expiretime_display =date("Y-m-d", $sub->expiretime);

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

    public static function create_poodll_sub($subscription){
        global $DB;

        //set up our school
        $school=false;
        if(isset($subscription->cf_schoolid)) {
            $school=$DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$subscription->cf_schoolid));
        }
        if(!$school){
            return false;
        }

        //set up our plan
        $plan = false;
        if(isset($subscription->cf_planid)) {
            $plan = self::get_plan($subscription->cf_planid);
        }else{
            $plan = self::fetch_poodllplan_from_upstreamplan($subscription->plan_id);
        }
        if(!$plan){
            return false;
        }


        $newsub= new \stdClass();
        $newsub->schoolid=$school->id;
        $newsub->planid=$plan->id;
        $newsub->upstreamownerid=$school->upstreamownerid;
        $newsub->upstreamsubid=$subscription->id;
        if($subscription->due_invoices_count>0){
            $newsub->status=constants::M_STATUS_PAYMENTDUE;
        }else{
            $newsub->status=constants::M_STATUS_ACTIVE;
        }
        
        if(is_number($subscription->current_term_end)){
            $newsub->expiretime=$subscription->current_term_end;
        }
        $newsub->payment= $subscription->subscription_items[0]->unit_price;
        $newsub->paymentcurr=$subscription->currency_code;
        $newsub->billinginterval=$plan->billinginterval;
        $newsub->timecreated=time();

        //this is where any sub specific stuff has to happen .. eg get LTI creds, or API user and secret
        $jsonfields = self::process_new_sub($school, $plan, $subscription);

        $newsub->jsonfields=$jsonfields;
        $newsub->hostedpage=json_encode($subscription);
        $newsub->timemodified=time();

        $subid = $DB->insert_record(constants::M_TABLE_SUBS,$newsub);
        return $subid;
    }

    public static function update_poodll_sub($upstreamsub, $poodllsub){
        global $DB;

        $update=false;
        if($poodllsub->paymentcurr != $upstreamsub->currency_code){
            $poodllsub->paymentcurr = $upstreamsub->currency_code;
            $update=true;
        }
        if($poodllsub->payment != $upstreamsub->subscription_items[0]->unit_price){
            $poodllsub->payment = $upstreamsub->subscription_items[0]->unit_price;
            $update=true;
        }

        $jsonobj= json_decode($poodllsub->jsonfields);
        if(!isset($jsonobj->due_invoices_count) || $jsonobj->due_invoices_count != $upstreamsub->due_invoices_count){
            $jsonobj->due_invoices_count = $upstreamsub->due_invoices_count;
            if( $upstreamsub->due_invoices_count==0 && $poodllsub->status==constants::M_STATUS_PAYMENTDUE){
                $poodllsub->status=constants::M_STATUS_ACTIVE;
            }elseif( $upstreamsub->due_invoices_count>0 && $poodllsub->status!=constants::M_STATUS_PAYMENTDUE){
                $poodllsub->status=constants::M_STATUS_PAYMENTDUE;
            }
            $update=true;
        }
        if(!isset($jsonobj->has_scheduled_changes) || $jsonobj->has_scheduled_changes != $upstreamsub->has_scheduled_changes){
            $jsonobj->has_scheduled_changes = $upstreamsub->has_scheduled_changes;
            $update=true;
        }
        $poodllsub->jsonfields = json_encode($jsonobj);

        if($poodllsub->expiretime != $upstreamsub->current_term_end){
            $poodllsub->expiretime = $upstreamsub->current_term_end;
            $update=true;
        }else{
            if($poodllsub->expiretime <time()){
                 if($poodllsub->status==constants::M_STATUS_ACTIVE){
                     $poodllsub->status=constants::M_STATUS_INACTIVE;
                     $update=true;
                 }
            }
        }

        if($update){
            $poodllsub->timemodified=time();
            $DB->update_record(constants::M_TABLE_SUBS,$poodllsub);
        }
    }

    public static function process_new_sub($school, $plan,$subscription){
        $obj = new \stdClass();
        $obj->due_invoices_count = $subscription->due_invoices_count;
        $obj->has_scheduled_changes= $subscription->has_scheduled_changes;
        return json_encode($obj);
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
            //this will fetch true poodll resold schools
            $schools = common::fetch_schools_by_reseller($reseller->id);
            foreach ($schools as $aschool){
                if($aschool->id==$schoolid){
                    $school = $aschool;
                }
            }
            //this will fetch a school that Poodll admin needs to work with
            if(!$school) {
                $school = $DB->get_record(constants::M_TABLE_SCHOOLS, array('id' => $schoolid));
            }

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


    public static function get_portalurl_by_sub($sub){
        global $CFG;
        //poodll reseller = client bought direct
        if($sub->school->reseller->resellertype==constants::M_RESELLER_POODLL) {
            $customerid = $sub->school->upstreamownerid;
        //otherwise its a 3rd party reseller
        }else{
            $customerid = $sub->school->reseller->upstreamuserid;
        }
        return chargebee::get_portalurl_by_upstreamid($customerid);
    }


    public static function fetch_poodll_resellerid(){
        global $DB;
        $poodllreseller = $DB->get_record(constants::M_TABLE_RESELLERS,array('resellertype'=>constants::M_RESELLER_POODLL));
        if($poodllreseller){
            return $poodllreseller->id;
        }else{
            return 0;
        }
    }
    public static function fetch_upstream_user_id($userid)
    {
        global $DB;
        $classroomuser = $DB->get_record(constants::M_TABLE_USERS,array('userid'=>$userid));
        if(!$classroomuser) {
            $upstreamuserid = 'user-' . $userid . '-' . random_string(8);
            $classroomuser=new \stdClass();
            $classroomuser->userid=$userid;
            $classroomuser->upstreamuserid=$upstreamuserid;
            $classroomuser->status=constants::M_STATUS_ACTIVE;
            $classroomuser->timecreated=time();
            $DB->insert_record(constants::M_TABLE_USERS,$classroomuser);
        }
        return $classroomuser->upstreamuserid;
    }

    public static function create_blank_school($ownerid=false,$reseller=false, $schoolname=false){
        global $USER, $DB;
        $school = new \stdClass();
        $school->timecreated = time();
        $school->timemodified = time();
        $school->jsonfields = '{}';

        if($reseller===false) {
           $school->resellerid = self::fetch_poodll_resellerid();
           $school->upstreamownerid = self::fetch_upstream_user_id($USER->id);
            if($ownerid==false){
                $school->ownerid = $USER->id;
            }else{
                $school->ownerid = $ownerid;
            }
        }else{
            $school->resellerid = $reseller->id;
            $school->upstreamownerid = $reseller->upstreamuserid;
            $school->ownerid = $reseller->userid;
        }

        //school name
        $owner = $DB->get_record('user',array('id'=>$school->ownerid));
        if($schoolname===false){
            $school->name= $owner->firstname . ' ' . $owner->lastname  .  ' ' .' school';
        }else{
            $school->name =$schoolname;
        }

        //make the school user over at cpapi ..
        $apiuserseed = "0123456789ABCDEF" . mt_rand(100, 99999);
        $apisecretseed = "0123456789ABCDEF" . mt_rand(100, 99999);
        $user_already_exists=true;
        $trycount=0;
        while($user_already_exists && $trycount<15) {
            $apiusername = str_shuffle($apiuserseed);
            $apisecret = str_shuffle($apisecretseed);
            $trycount++;
            $user_already_exists = self::exists_cpapi_user($apiusername);
        }

        //if we get a name clash 15 times we are stuck somehow, so cancel
        if($user_already_exists){return false;}

        //create user
        $ret = self::create_cpapi_user($apiusername,
                $apisecret,
                $owner->firstname,
                $owner->lastname,
                $owner->email);

        $school->apiuser=$apiusername;
        $school->apisecret=$apisecret;
        $id = $DB->insert_record(constants::M_TABLE_SCHOOLS,$school);
        if($id){
            $school->id = $id;
            return $school;
        }else{
            return false;
        }
    }

    public static function do_sync_subs($trace)
    {
        global $DB;
        //sync subs that appear to have expired or have a payment due
        $syncsubs = $DB->get_records_sql("SELECT * from {" . constants::M_TABLE_SUBS . "} WHERE (expiretime < :now AND status = :activestatus) OR status = :paymentduestatus" ,
            array('now'=>time(),'activestatus'=>constants::M_STATUS_ACTIVE,'paymentduestatus'=>constants::M_STATUS_PAYMENTDUE));


        $trace->output('chargebee syncing: ' . count($syncsubs));
        foreach($syncsubs as $poodllsub){
            $trace->output('syncing:' . $poodllsub->upstreamsubid);
            chargebee::sync_sub($poodllsub);
        }
    }

    public static function exists_cpapi_user($username){

        //sanitize username
        $username = strtolower($username);
        $ret = cpapi_helper::get_moodle_users($username);
        $exists =false;
        if($ret && property_exists($ret,'users')){
            if(count($ret->users)>0){
                //$user =$ret->users[0];
                $exists =true;
            }
        }
        return $exists;

    }

    /*
 * Create a new standard user on cloud poodll com, and by extension trigger creation of cpapi user
 */
    public static function create_cpapi_user($username,$password,$firstname,$lastname,$email){


        //sanitize username
        $username = strtolower($username);
        $ret = cpapi_helper::make_moodle_user($username,
                $password,
                $firstname,
                $lastname,
                $email);
        return $ret;
    }


    /*
    * Update user with CPAPI specifics
    */
    public static function update_cpapi_userdeets($username, $firstname,$lastname, $email){

        //sanitize username
        $username = strtolower($username);

        //update the user
        $ret = cpapi_helper::update_cpapi_user($username,
                $firstname,
                $lastname,
                $email,
                0,
                0,
                0,
                0,
                0
        );
        return $ret;
    }

    /*
     * Update user with CPAPI specifics
     */
    public static function update_cpapi_fulluser($username, $firstname,$lastname, $email,
            $expiretime=0, $subscriptionid=0, $transactionid=0,
            $accesskeyid='',$accesskeysecret=''){

        //sanitize username
        $username = strtolower($username);

        //update the user
        $expiry = strtotime($expiretime);
        $ret = cpapi_helper::update_cpapi_user($username,
                $firstname,
                $lastname,
                $email,
                $expiry,
                $subscriptionid,
                $transactionid,
                $accesskeyid,
                $accesskeysecret
        );
        return $ret;
    }

    /*
     * Reset and return the user's API secret
     */
    public static function reset_cpapi_secret($userid, $username,$currentsecret){
        $ch = new cpapi_helper();
        $secret ="";

        //sanitize username
        $username = strtolower($username);

        $ret = cpapi_helper::reset_cpapi_secret($username,$currentsecret);
        if ($ret && property_exists($ret,'returnCode') && $ret->returnCode==0) {
            $secret = $ret->returnMessage;
            poodll_write_secret_locally($userid,$secret);
            poodll_log('updated cpapi secret:\r\n');
        }else{
            poodll_log('did not update cpapi secret:\r\n');
        }
        return $secret;
    }




    /*
     * Set/update the user's registered sites
     */
    public static function update_cpapi_sites($username, $url1, $url2, $url3, $url4, $url5){

        //check for blacklisted URL
        $blacklist =['XXXXSITE.edu.vn'];
        foreach($blacklist as $badurl){
            if(!empty($url1) && strpos($url1,$badurl)>0) {
                $url1= '';
            }
            if(!empty($url2) && strpos($url2,$badurl)>0) {
                $url2= '';
            }
            if(!empty($url3) && strpos($url3,$badurl)>0) {
                $url3= '';
            }
            if(!empty($url4) && strpos($url4,$badurl)>0) {
                $url4= '';
            }
            if(!empty($url5) && strpos($url5,$badurl)>0) {
                $url5= '';
            }
        }


        //sanitize username
        $username = strtolower($username);
        $ret = cpapi_helper::update_cpapi_sites($username,$url1,$url2,$url3,$url4,$url5);
        return $ret;
    }




}//end of class
