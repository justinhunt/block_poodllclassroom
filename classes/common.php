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

require_once("$CFG->dirroot/user/externallib.php");


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



    public static function fetch_truefalse(){
        return array(0=>get_string('false',constants::M_COMP),
            1=>get_string('true',constants::M_COMP));
    }

    public static function fetch_billingintervals(){
        return array(constants::M_BILLING_YEARLY=>get_string('yearly',constants::M_COMP),
                constants::M_BILLING_MONTHLY=>get_string('monthly',constants::M_COMP),
                constants::M_BILLING_DAILY=>get_string('daily',constants::M_COMP));
    }

    public static function fetch_planfamilies(){
        return array(constants::M_FAMILY_CLASSROOM=>constants::M_FAMILY_CLASSROOM,
            constants::M_FAMILY_LTI=>constants::M_FAMILY_LTI,
            constants::M_FAMILY_LANG=>constants::M_FAMILY_LANG,
            constants::M_FAMILY_MEDIA=>constants::M_FAMILY_MEDIA,
            constants::M_FAMILY_ESSENTIALS=>constants::M_FAMILY_ESSENTIALS,
            constants::M_FAMILY_EC =>constants::M_FAMILY_EC,
            constants::M_FAMILY_API =>constants::M_FAMILY_API ,
            constants::M_FAMILY_STANDALONE =>constants::M_FAMILY_STANDALONE,
            constants::M_FAMILY_LEGACY =>constants::M_FAMILY_LEGACY
            );
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
        $owners = $DB->get_records('user', array(),'firstname, lastname ASC');
        $ret = [];
        foreach($owners as $owner){
            $ret[$owner->id]=$owner->firstname . ' ' . $owner->lastname . '(' . $owner->id . ')' ;
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

    public static function fetch_plans_by_platform($platform, $planfamily='ALL', $onlyvisibleplans=false){
        global $DB;

        $params = ['platform'=>$platform];
        $families = self::fetch_planfamilies();
        if(array_key_exists($planfamily,$families)){
            $params['planfamily'] = $planfamily;
        }
        if($onlyvisibleplans){
            $params['showcheckout'] = 1;
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
            $merged_subs =[];
            foreach($subs as $sub){
                $merged_sub= common::merge_poodll_upstream_sub($sub);
                if($merged_sub) {
                    $merged_subs[] = $merged_sub;
                }
            }
            return $merged_subs;
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
            $merged_subs =[];
            foreach($subs as $sub){
                $merged_sub= common::merge_poodll_upstream_sub($sub);
                if($merged_sub) {
                    $merged_subs[] = $merged_sub;
                }
            }
            return $merged_subs;
        }else{
            return [];
        }
    }

    //Here we merge the upstream and local sub into one object
    //we also sync the local sub with the remote one and save it. But the real point is that we only use
    //fresh info from upstream, not sync'd data. We ought to complete remove fields from subs table apart from id, upstreamsubid and planid
    //thats a bit scarey because occasionally we do an "all subs" call and in future might want to report. So we sync it but try to only use fresh data
    public static function merge_poodll_upstream_sub($poodllsub){

        $upstream = chargebee_helper::fetch_chargebee_sub($poodllsub->upstreamsubid);
        //we should not get in this situation, but its possible at least in the early days to have some out of sync
        if(!$upstream || !isset($upstream->subscription)){
            return false;
        }

        $upstreamsub=$upstream->subscription;
       // $upstreamuser=$upstream->customer;
        $poodllsub= self::update_poodllsub_from_upstream($poodllsub, $upstreamsub);

        //add Poodll Sub Fields to Upstream Sub (for display ultimately)
        $upstreamsub->upstreamsubid = $upstreamsub->id;
        $upstreamsub->id = $poodllsub->id;
        $upstreamsub->schoolid = $poodllsub->schoolid;
        $upstreamsub->payment = $poodllsub->payment;
        $upstreamsub->paymentcurr = $poodllsub->paymentcurr;
        $upstreamsub->billinginterval = $poodllsub->billinginterval;
        $upstreamsub->expiretime = $poodllsub->expiretime;
        $upstreamsub->planid = $poodllsub->planid;
        $upstreamsub->jsonfields = $poodllsub->jsonfields;
        $upstreamsub->timecreated = $poodllsub->timecreated;
        $upstreamsub->timemodified = $poodllsub->timemodified;


        if($upstreamsub->has_scheduled_changes){
            $upstreamsub->scheduled_sub= new \stdClass();
            $scheduled=chargebee_helper::fetch_scheduled_chargebee_sub($poodllsub->upstreamsubid);
            $scheduled_sub=$scheduled->subscription;
            //$scheduled_user=$scheduled->customer;
           //get the new payment amount
            $upstreamsub->scheduled_sub->payment = $scheduled_sub->subscription_items[0]->amount;
            //get our plan
            $scheduledplanid = common::fetch_upstreamplanid_from_upstreamsub($scheduled_sub);
            $poodllplan = self::fetch_poodllplan_from_upstreamplan($scheduledplanid );
            $upstreamsub->scheduled_sub->planname = $poodllplan->name;
            //get our billing unit
            $upstreamsub->scheduled_sub->billinginterval = $poodllplan->billinginterval;
        }

        return $upstreamsub;

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
                $ret[$user->id]=$user->firstname . ' ' . $user->lastname . '(' . $user->id . ')' ;
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

        $sql = 'SELECT school.*, u.firstname as ownerfirstname, u.lastname as ownerlastname, u.email as owneremail ';
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

    public static function add_expiring_sub_to_schools($schools){
        global $DB;

        foreach($schools as $school){
            $sql =  ' SELECT MIN(expiretime) as expiretime FROM {'. constants::M_TABLE_SUBS .'} sub ';
            $sql .= ' WHERE sub.schoolid = :schoolid AND NOT sub.status IN (\'inactive\',\'cancelled\') ';
            $sql .= ' GROUP BY schoolid';
            $subs= $DB->get_records_sql($sql,['schoolid'=>$school->id]);
            if($subs){
                $sub = array_shift($subs);
                $school->nextexpiry=$sub->expiretime;
            }
        }
        return $schools;
    }

    public static function fetch_schools(){
        global $DB;

        $sql = 'SELECT school.*, u.firstname as ownerfirstname, u.lastname as ownerlastname, u.email as owneremail ';
        $sql .= 'from {'. constants::M_TABLE_SCHOOLS .'} school ';
        $sql .= 'INNER JOIN {user} u ON u.id = school.ownerid ';
        $schools=$DB->get_records_sql($sql);

        if($schools) {
            return $schools;
        }else{
            return [];
        }
    }

    //TO DO: remove this , no longer used
    public static function xxget_poodllsubs_by_currentuser(){
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
        global $DB, $USER, $OUTPUT;
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

            //if super admin we can edit sub //REMOVE THIS EVENTUALLY
            if($USER->id==2) {
                $urlparams = array('id' => $sub->id,'type'=>'sub','returnurl' => '');
                $sub->editsuburl = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php', $urlparams),
                    $OUTPUT->pix_icon('t/edit', get_string('edit')),
                    array('title' => get_string('edit')));
            }
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
                case constants::M_BILLING_DAILY:
                    if($sub->plan->hasfreetrial){
                        $sub->plan->period_display=get_string('free',constants::M_COMP);
                        $sub->free=true;
                    }else{
                        $sub->plan->period_display=get_string('daily',constants::M_COMP);
                    }

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

                case constants::M_STATUS_ACTIVE:
                    $sub->status_display=get_string('active_status',constants::M_COMP);
                    break;
                case constants::M_STATUS_FUTURE:
                    $sub->status_display=get_string('future_status',constants::M_COMP);
                    break;
                case constants::M_STATUS_IN_TRIAL:
                    $sub->status_display=get_string('intrial_status',constants::M_COMP);
                    $sub->intrial=true;
                    break;
                case constants::M_STATUS_NONRENEWING:
                    $sub->status_display=get_string('nonrenewing_status',constants::M_COMP);
                    //because of the way we have set up free trials, between end of trial and cancellation there will be a day
                    // the intrial flag turns on the upgrade button so we want to show that.
                    if($sub->plan->hasfreetrial){
                        $sub->intrial=true;
                    }
                    break;
                case constants::M_STATUS_PAUSED:
                    $sub->status_display=get_string('paused_status',constants::M_COMP);
                    break;
                case constants::M_STATUS_CANCELLED:
                    $sub->status_display=get_string('cancelled_status',constants::M_COMP);
                    //if its a cancelled free trial it would be good to show something here, but they may already have taken a new sub
                    if($sub->plan->hasfreetrial){
                        //do not do anything special
                    }

                    break;
                case constants::M_STATUS_NONE:
                default:
                    $sub->status_display='-';
            }

            if(isset($sub->due_invoices_count) && $sub->due_invoices_count>0){
                $sub->status_display .= '<br><span class="block_poodllclassroom_paymentdue">' . get_string('paymentdue',constants::M_COMP) .'</span>';
            }

            if(isset($sub->cancelled_at) && $sub->status != constants::M_STATUS_CANCELLED && $sub->cancelled_at > time() ){
                $sub->status_display .= '<br><span class="block_poodllclassroom_willcancel">' . get_string('willcancel',constants::M_COMP) .'</span>';
            }
            if(isset($sub->scheduled_sub)){
                $a = new \stdClass();
                switch($sub->scheduled_sub->billinginterval){
                    case constants::M_BILLING_YEARLY:
                        $a->period_display=get_string('yearly',constants::M_COMP);
                        break;
                    case constants::M_BILLING_MONTHLY:
                        $a->period_display=get_string('monthly',constants::M_COMP);
                        break;
                    case constants::M_BILLING_DAILY:
                        $a->period_display=get_string('free',constants::M_COMP);
                        break;
                    default:
                        $a->period_display='';

                }

                //get the new payment amount
                if($sub->paymentcurr!=='JPY'){
                    $scheduled_amount = floatval($sub->scheduled_sub->payment) / 100;
                }
                $fmt = \numfmt_create( 'en_US', \NumberFormatter::CURRENCY );
                $a->payment_display =  \numfmt_format_currency($fmt, $scheduled_amount , $sub->paymentcurr)."\n";

                //get our plan
                $a->planname = $sub->scheduled_sub->planname;

                $sub->status_display .= '<br><span class="block_poodllclassroom_subchanges">' .
                    get_string('subwillchange',constants::M_COMP, $a) .
                    '</span>';

            }

            //expiry date
            $sub->expiretime_display =date("Y-m-d", $sub->expiretime);

            //time created
            $sub->timecreated_display =date("Y-m-d H:i:s", $sub->timecreated);

            //lti subs
            if(isset($sub->extra) && isset($sub->extra->ltidetails) && is_array($sub->extra->ltidetails)){
                $sub->extra->cartridgeurl=$sub->extra->ltidetails[0]->toolurl;
                $sub->extra->consumerkey=$sub->extra->ltidetails[0]->consumerkey;
                $sub->extra->consumersecret=$sub->extra->ltidetails[0]->secret;
            }

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

    //this is where any sub specific stuff has to happen .. eg get LTI creds, or API user and secret
    public static function respond_to_updated_upstream_sub($poodllsub, $upstreamsub){
        global $DB;
        $plan = self::get_plan($poodllsub->planid);
        $jsonfields = self::process_updated_sub($poodllsub, $upstreamsub, $plan);

        $poodllsub->jsonfields = $jsonfields;
        $poodllsub->hostedpage = json_encode($upstreamsub);

        $ret = $DB->update_record(constants::M_TABLE_SUBS, $poodllsub);
        return $ret;
    }

    //here we update the local sub table with details from upstream sub
    //we try to never use local sub info, and to always pull afresh, eventually remove most fields from table
    public static function update_poodllsub_from_upstream($poodllsub,$upstreamsub){
        global $DB;
        $ret = false;
        $upstreamplanid = common::fetch_upstreamplanid_from_upstreamsub($upstreamsub);
        $plan = self::fetch_poodllplan_from_upstreamplan($upstreamplanid);
        if($plan && $poodllsub && $upstreamsub) {

            $poodllsub->planid = $plan->id;
            $poodllsub->status = $upstreamsub->status;
            $poodllsub->expiretime= self::extract_expire_time($upstreamsub);
            $poodllsub->payment = $upstreamsub->subscription_items[0]->amount;
            $poodllsub->paymentcurr = $upstreamsub->currency_code;
            $poodllsub->billinginterval = $plan->billinginterval;
            $poodllsub->timemodified = time();
            $ret = $DB->update_record(constants::M_TABLE_SUBS, $poodllsub);
        }
        return $ret ? $poodllsub : false;
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

    public static function create_poodll_sub($subscription, $currency_code, $amount_paid, $upstreamownerid, $downstreamschoolid=false){

        global $DB;

        //set up our school
        $school=false;
        //we append this to get checkout existing, probably wont come in here, unless something went weird at hostedpage welcome back
        if($downstreamschoolid) {
            $school=$DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$downstreamschoolid));
        }elseif($upstreamownerid){
            $schools = self::get_schools_by_upstreamownerid($upstreamownerid);
            if( $schools && count($schools)==1){
                $school = array_shift($schools);
            }
        }
        if(!$school){
            return false;
        }

        //Only some subs have total=dues nd current term end. often buried in items. here we fetch them
        if(!isset($subscription->total_dues)){
            $subscription = self::add_fields_to_sub($subscription);
        }

        //set up our plan
        $plan = false;
        //we append this to get checkout existing, probably wont come in here, unless something went weird at hostedpage welcome back
        if(isset($subscription->cf_planid)) {
            $plan = self::get_plan($subscription->cf_planid);
        }else{
            if(isset($subscription->plan_id)){
                $plan_id = $subscription->plan_id;
            }else{
                $plan_id = self::fetch_upstreamplanid_from_upstreamsub($subscription);
            }
            $plan = self::fetch_poodllplan_from_upstreamplan($plan_id);
        }
        if(!$plan){
            return false;
        }


        $newsub= new \stdClass();
        $newsub->schoolid=$school->id;
        $newsub->planid=$plan->id;
        $newsub->upstreamownerid=$school->upstreamownerid;
        $newsub->upstreamsubid=$subscription->id;
        $newsub->status=$subscription->status;
        $newsub->expiretime = self::extract_expire_time($subscription);
        $newsub->payment= $amount_paid;
        $newsub->paymentcurr=$currency_code;
        $newsub->billinginterval=$plan->billinginterval;
        if(isset($subscription->created_at)){
            $newsub->timecreated=$subscription->created_at;
        }else{
            $newsub->timecreated=time();
        }


        //this is where any sub specific stuff has to happen .. eg get LTI creds, or API user and secret
        //Disable while we import from CB
        $jsonfields = self::process_new_sub($school, $plan, $subscription);

        $newsub->jsonfields=$jsonfields;
        $newsub->hostedpage=json_encode($subscription);
        $newsub->timemodified=time();

        $subid = $DB->insert_record(constants::M_TABLE_SUBS,$newsub);
        return $subid;
    }

    public static function add_fields_to_sub($upstreamsub){

        if(!isset($upstreamsub->total_dues) ||!is_numeric($upstreamsub->total_dues)){
            $upstreamsub->total_dues=$upstreamsub->subscription_items[0]->amount;
        }
        return $upstreamsub;
    }

    public static function process_new_sub($school, $plan,$subscription){
        global $DB;

        $obj = new \stdClass();
        $obj->due_invoices_count = $subscription->due_invoices_count;
        $obj->has_scheduled_changes= $subscription->has_scheduled_changes;

        switch($plan->platform){
                case constants::M_PLATFORM_MOODLE:
                    $username = strtolower($school->apiuser);
                    $accesskeyid='xxxxxx';
                    $accesskeysecret='yyyyyy';
                    $subscriptionid = $plan->poodllplanid; //this is the numeric id .. of the old memberpress system which cloudpoodll still keys on
                    $transactionid = 999;//$subscription->id would be the one, but its int only at this stage
                    $expiretime=self::extract_expire_time($subscription);
                    $theuser = $DB->get_record('user', array('id'=>$school->ownerid));
                    $ret = cpapi_helper::update_cpapi_user($username,$theuser->firstname,$theuser->lastname,$theuser->email,
                        $expiretime,$subscriptionid,$transactionid,$accesskeyid,$accesskeysecret);
                    break;

                case constants::M_PLATFORM_LTI:
                    $expiretime=self::extract_expire_time($subscription);
                    $ret = lti_helper::update_lti_sub($school->name, $subscription->customer_id, $subscription->id,$plan->upstreamplan,$expiretime);
                    if($ret && isset($ret->error) && !$ret->error){
                        $obj->ltidetails=$ret->ltidetails;
                    }
                    break;

            default:
        }

        return json_encode($obj);
    }

    //The chargebee sub could be in a trial or paid or other state
    //so we can not rely on the expire time to be the same property for all upstream subs
    public static function extract_expire_time($upstreamsub){

        if (isset($upstreamsub->current_term_end) && is_number($upstreamsub->current_term_end)) {
            $expiretime = $upstreamsub->current_term_end;
        }elseif($upstreamsub->status==constants::M_STATUS_IN_TRIAL && isset($upstreamsub->subscription_items[0]->trial_end)){
            $expiretime = $upstreamsub->subscription_items[0]->trial_end;
        }elseif(isset($upstreamsub->subscription_items[0]->trial_end) && $upstreamsub->status==constants::M_STATUS_CANCELLED) {
            $expiretime = $upstreamsub->subscription_items[0]->trial_end;
        }else{
            //getting here would be unthinkable ..  but who knows
            $expiretime =$upstreamsub->next_billing_at + YEARSECS;
        }
        return $expiretime;
    }

    public static function process_updated_sub($poodllsub,$upstreamsub,$plan){
        global $DB;

        $school = self::get_school_by_sub($poodllsub);
        switch($plan->platform){
            case constants::M_PLATFORM_MOODLE:
                $username = strtolower($school->apiuser);
                $accesskeyid='xxxxxx';
                $accesskeysecret='yyyyyy';
                $subscriptionid = $plan->poodllplanid; //this is the numeric id .. of the old memberpress system which cloudpoodll still keys on
                $transactionid = 999;//$subscription->id would be the one, but its int only at this stage
                $expiretime=self::extract_expire_time($upstreamsub);
                $theuser = $DB->get_record('user', array('id'=>$school->ownerid));
                $ret = cpapi_helper::update_cpapi_user($username,$theuser->firstname,$theuser->lastname,$theuser->email,
                    $expiretime,$subscriptionid,$transactionid,$accesskeyid,$accesskeysecret);
                break;

            case constants::M_PLATFORM_LTI:
                $expiretime=self::extract_expire_time($upstreamsub);
                $ret = lti_helper::update_lti_sub($school->name, $upstreamsub->customer_id, $upstreamsub->id,$plan->upstreamplan,$expiretime);
                if($ret && isset($ret->error) && !$ret->error){
                    //not sure what to do here, so will do nothing!
                    //$obj->ltidetails=$ret->ltidetails;
                }
                break;

            default:
        }

        $obj = new \stdClass();
        $obj->due_invoices_count = $upstreamsub->due_invoices_count;
        $obj->has_scheduled_changes= $upstreamsub->has_scheduled_changes;
        return json_encode($obj);
    }

    public static function fetch_upstreamplanid_from_upstreamsub($subscription){
        $plan_id = $subscription->subscription_items[0]->item_price_id;
        $currency = $subscription->currency_code ? $subscription->currency_code : 'USD';
        //remove any appended price plan IDs
        $plan_id = str_replace('-' .  $currency . '-Yearly','',$plan_id);
        $plan_id = str_replace('-' .  $currency . '-Monthly','',$plan_id);
        $plan_id = str_replace('-' .  $currency . '-Daily','',$plan_id);
        return $plan_id;
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

    public static function get_school_by_owner($user){
        global $DB;
        $school = $DB->get_record(constants::M_TABLE_SCHOOLS,array('ownerid'=>$user->id));
        if($school) {
            return $school;
        }else{
            return false;
        }

    }

    public static function get_schools_by_upstreamownerid($upstreamownerid){
        global $DB;
        $schools = $DB->get_records(constants::M_TABLE_SCHOOLS,array('upstreamownerid'=>$upstreamownerid));
        if($schools) {
            return $schools;
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

    public static function curl_fetch($url, $postdata = false, $username='', $forceget=false) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();

        if(!empty($username)) {
            $curl->setopt(array('CURLOPT_HTTPAUTH'=>CURLAUTH_BASIC));
            $curl->setopt(array('CURLOPT_USERPWD'=> $username . ":"));
        }

        if($postdata) {
            $postdatastring = http_build_query($postdata, '', '&');
            if($forceget){
                $result = $curl->get($url, $postdata);
            }else{
                $result = $curl->post($url, $postdatastring);
            }

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
        return chargebee_helper::get_portalurl_by_upstreamid($customerid);
    }

    public static function poodll_reginald_fetch_connection(){
        global $CFG;

        $servername =  $CFG->reginald_servername;// reginald_creds::POODLL_MYSQL_SERVERNAME;
        $dbname = $CFG->reginald_dbname;//reginald_creds::POODLL_MYSQL_DBNAME;
        $username =  $CFG->reginald_username; //reginald_creds::POODLL_MYSQL_USERNAME;
        $password = $CFG->reginald_password; //reginald_creds::POODLL_MYSQL_PASSWORD;

        // Create connection
        $conn = new \mysqli($servername, $username, $password, $dbname);
        $conn->set_charset("utf8");
        // Check connection
        if ($conn->connect_errno) {
            error_log("Connection failed: " . $conn->connect_error);
            return false;
        }else{
            return $conn;
        }
    }


    public static function sync_siteurls_from_upstreamid($upstreamownerid){
        global $CFG, $DB;
        $ret=[];
        $ret['id']=$upstreamownerid;
        $ret['success']=true;
        $ret['message']='OK';

        //do we already gots one like dis one, return
        $existing_schools = self::get_schools_by_upstreamownerid($upstreamownerid);
        if(!$existing_schools ){
            $ret['success']=false;
            $ret['message']='We dont have that school:';
            return $ret;
        }
        $school=array_shift($existing_schools );

        //fetch user, if no user then ... out of here man
        $upstream_user = chargebee_helper::fetch_chargebee_user($upstreamownerid);
        //upstream_user->customer has first_name / last_name /email / company /
        if(!$upstream_user || !isset($upstream_user->customer) || !isset($upstream_user->customer->email) ){
            $ret['success']=false;
            $ret['message']='No valid chargebee user of that ID found: ' . $upstreamownerid;
            return $ret;
        }

        //We should have this user
        $existing_user = $DB->get_record('user', array('email'=>$upstream_user->customer->email));
        if(!$existing_user){
                $ret['success'] = false;
                $ret['message'] = 'We DONT already have a moodle user with that email here: ' . $upstream_user->customer->email;
            return $ret;
        }

        //Do some syncing
       if(false) {
               $ret['success']=false;
               $ret['message']='We had nothing to do here';
               return $ret;
       }else{
           $ret['success']=false;
           $ret['message']='Site URL2 was empty';
           return $ret;
       }
        $ret['success']=false;
        $ret['message']='ended with nothing, possibly a DB error';
        return $ret;
    }

    public static function create_school_from_upstreamid($upstreamownerid, $startsiteurl=false){
        global $CFG, $DB, $OUTPUT;
        $ret=[];
        $ret['id']=$upstreamownerid;
        $ret['success']=true;
        $ret['message']='OK';

        //do we already gots one like dis one, return
        $existing_schools = self::get_schools_by_upstreamownerid($upstreamownerid);
        if($existing_schools  && !empty($existing_schools )){
            $ret['success']=false;
            $ret['message']='We already have that school:';
            foreach($existing_schools as $school){
                $ret['message'] .= $school->name . ' ';
            }
            return $ret;
        }

        //fetch user, if no user then ... out of here man
        $upstream_user = chargebee_helper::fetch_chargebee_user($upstreamownerid);
        //upstream_user->customer has first_name / last_name /email / company /
        if(!$upstream_user || !isset($upstream_user->customer) || !isset($upstream_user->customer->email) ){
            $ret['success']=false;
            $ret['message']='No valid chargebee user of that ID found: ' . $upstreamownerid;
            return $ret;
        }

        //at this point we have an upstream user and a new school to make ..
        //ftg_1982@hotmail.com user
        //if user already exists here .. someone has some explaining to do. Lets check by email ...
        $existing_user = $DB->get_record('user', array('email'=>$upstream_user->customer->email));
        if($existing_user){
            //in this scenario we have a user on our system with the customer email,
            // but we do not have a school with the users upstream id
            //do we have a school for this user? if so it was created locally and no upstream id associated yet
           $school = self::get_school_by_owner($existing_user);
           if($school && (empty($school->upstreamownerid) ||$school->upstreamownerid=="unspecified" )){
               $school->upstreamownerid=$upstreamownerid;
               $ret['success'] = $DB->update_record(constants::M_TABLE_SCHOOLS,$school);
               $ret['message'] = 'Attempted update of user school with upstreamid: ' . $upstream_user->customer->email;

           }else {
               $ret['success'] = false;
               $ret['message'] = 'We already have a moodle user with that email here: ' . $upstream_user->customer->email;
           }
            return $ret;
        }

        //if we have disabled comunications with cpapi (for testing etc) .. create bogus deets
        $adminconfig = get_config(constants::M_COMP);
        if (!$adminconfig->enablecpapievents) {
            $legacyuser = [];
            $legacyuser['apiuser'] = 'bogus' . cpapi_helper::create_random_apiuser();
            $legacyuser['apiusername'] = $legacyuser['apiuser'];
            $legacyuser['apisecret'] = $legacyuser['apiuser'];
            $legacyuser['siteurls'] = [];

            //otherwise connect and create a real user
        } else {
            //lets create a user
            //create user
            $apiusername = cpapi_helper::create_random_apiuser();
            $legacyuser = cpapi_helper::create_cpapi_user(
                $upstream_user->customer->first_name,
                $upstream_user->customer->last_name,
                $upstream_user->customer->email,
                $apiusername);
            if ($legacyuser) {
                $legacyuser = get_object_vars($legacyuser);
            }

            //if that failed lets leave
            if (!$legacyuser || empty($legacyuser['apiuser'])) {
                $ret['success'] = false;
                $ret['message'] = 'could not fetch nor create a legacy user for: ' . $upstream_user->customer->email;
                return $ret;
            }

            //if we have a site URL for our new site, lets add that now
            if($startsiteurl){
                $startsiteurl = strtolower($startsiteurl);
                if(strpos($startsiteurl,'http')!==0){
                    $startsiteurl='https://' . $startsiteurl;
                }
                cpapi_helper::update_cpapi_sites($legacyuser['apiuser'],$startsiteurl,'','','','');
                $legacyuser['siteurls'][]=$startsiteurl;
            }
        }

        $newuser=[];
        $newuser['firstname']=$upstream_user->customer->first_name;
        $newuser['firstnamephonetic']=$upstream_user->customer->first_name;
        $newuser['lastname']=$upstream_user->customer->last_name;
        $newuser['lastnamephonetic']=$upstream_user->customer->last_name;
        $newuser['alternatename'] = $upstream_user->customer->last_name . ' ' . $upstream_user->customer->first_name;
        $newuser['username']=strtolower($upstream_user->customer->email);
        $newuser['auth']='manual';

        //its unlikely the username/email exists .. but it might happen, we try 5 times to change it before using the apiuser
        $usernameexists = $DB->get_record('user', array('username'=> $newuser['username']));
        $inc=0;
        while($usernameexists && $inc < 5) {
            $bits =  explode('@', $newuser['username']);
            $bits[0] = $bits[0] . '+' . $inc;
            $newuser['username'] = $bits[0] . '@' . $bits[1];
            $inc++;
            $usernameexists = $DB->get_record('user', array('username'=> $newuser['username']));
        }
        if($usernameexists){
            $newuser['username'] =  strtolower($legacyuser['apiuser']);
        }

        //If we set createpassword, Moodle will make a temp password and email it.
        //we used to do this and force them to login and set their site URL and pick up API keys
        //but people just got lost. And the email was hard to configure cos' we could not set extra data fields
        //So now we get siteurl from startsiteurl (if they come the right way)
        // and just send all the info including API user and secret in the email from here
       // $sendpoodllwelcome= $startsiteurl && !empty($startsiteurl);
        $sendpoodllwelcome=true;
        if( $sendpoodllwelcome){
            $newuser['password']= generate_password();
        }else{
            $newuser['createpassword']=true;
        }

        $newuser['email']=$upstream_user->customer->email;

        // go here and follow the white rabbit setnew_password_and_mail for a strategy to get old users onto new platform
        $users = \core_user_external::create_users([$newuser]);

        if($users && count($users)==1){
            $user = array_shift($users);
            $school = new \stdClass();
            $school->timecreated = time();
            $school->timemodified = time();
            $school->jsonfields = '{}';
            $school->resellerid = self::fetch_poodll_resellerid();
            $school->upstreamownerid = $upstreamownerid;
            $school->ownerid =  $user['id'];
            $school->apiuser=$legacyuser['apiuser'];
            $school->apisecret=$legacyuser['apisecret'];
            $school->siteurls=json_encode($legacyuser['siteurls']);
            if(!empty($legacyuser['schoolname'])){
                $school->name=$legacyuser['schoolname'];
            }else{
                $school->name= $upstream_user->customer->first_name . ' ' . $upstream_user->customer->last_name  .  ' ' .' school';
            }
            $id = $DB->insert_record(constants::M_TABLE_SCHOOLS,$school);

            //send an email to user about their account
            if( $sendpoodllwelcome){
                if($startsiteurl) {
                    $school->startsiteurl = $startsiteurl;
                }
                $theuser = $DB->get_record('user',array('id'=>$school->ownerid ));
                $school->first_name = $upstream_user->customer->first_name;
                $school->last_name = $upstream_user->customer->last_name;
                $school->username = $theuser->username;
                $school->email = $theuser->email;
                $school->password = $newuser['password'];
                $mailsubject = get_string('poodllwelcomemailsubject', constants::M_COMP);
                $mailcontenttext = $OUTPUT->render_from_template('block_poodllclassroom/poodllwelcomemail', $school);
                $mailcontenthtml = $OUTPUT->render_from_template('block_poodllclassroom/poodllwelcomemailhtml', $school);
                $supportuser = \core_user::get_support_user();

                email_to_user($theuser, $supportuser, $mailsubject, $mailcontenttext,$mailcontenthtml);

            }

            //if we could not create a school, yay, else return false
            if($id){
                $school->id = $id;
                $ret['message']= "created: " . $school->name;
                return $ret;//$school;
            }else{
                $ret['success']=false;
                $ret['message']='We failed to create the school for some reason';
                return $ret;
            }
        //if we could not create a Moodle user we have failed and return false
        }else{
            $ret['success']=false;
            $ret['message']='We failed to create a Moodle user for some reason';
            return $ret;
        }
    }

    public static function create_sub_from_upstreamid($upstreamsubid,$downstreamschoolid=false){
        global $CFG;
        //dont create a subscription twice, that would be bad ...
        $poodllsub = common::get_poodllsub_by_upstreamsubid($upstreamsubid);
        if($poodllsub){
            $ret['success']=false;
            $ret['message']='We already have that sub: ' .$upstreamsubid ;
            return $ret;
        }
        $upstream_sub = chargebee_helper::fetch_chargebee_sub($upstreamsubid);
        if($upstream_sub && isset($upstream_sub->subscription)) {
            $customer =$upstream_sub->customer;
            $currency_code = $upstream_sub->subscription->currency_code;
            $amount_paid = $upstream_sub->subscription->subscription_items[0]->amount;
            $newsubid = self::create_poodll_sub($upstream_sub->subscription, $currency_code, $amount_paid,$customer->id,$downstreamschoolid);
            if($newsubid ){
                $ret['success']=true;
                $ret['message']='Created local sub(' . $newsubid  . ') from: ' .$upstreamsubid ;
                return $ret;
            }else{
                $ret['success']=false;
                $ret['message']='We failed to write that sub locally: ' .$upstreamsubid ;
                return $ret;
            }
        }else{
            $ret['success']=false;
            $ret['message']='cloud not fetch that sub from upstream: ' .$upstreamsubid ;
            return $ret;
        }
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
        return self::update_upstreamuser_moodleuser_link($userid);
    }

    public static function update_upstreamuser_moodleuser_link($userid,$upstreamuserid=''){
        global $DB;
        $classroomuser = $DB->get_record(constants::M_TABLE_USERS,array('userid'=>$userid));
        if(!$classroomuser) {
            //this should totally never ever happen
            if(empty($upstreamuserid)){
                $upstreamuserid =  'user-' . $userid . '-' . random_string(8);
            }
            $classroomuser=new \stdClass();
            $classroomuser->userid=$userid;
            $classroomuser->upstreamuserid=$upstreamuserid;
            $classroomuser->status=constants::M_STATUS_ACTIVE;
            $classroomuser->timecreated=time();
            $DB->insert_record(constants::M_TABLE_USERS,$classroomuser);
        }else{
            if($classroomuser->upstreamuserid != $upstreamuserid && !empty($upstreamuserid)){
                $classroomuser->upstreamuserid = $upstreamuserid;
                $DB->update_record(constants::M_TABLE_USERS,$classroomuser);
            }
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
        if($reseller!==false) {
            $cpapiemail = str_replace('@','_' . time() . '@' ,$owner->email);
            $cpapiusername = cpapi_helper::create_random_apiuser();
        }else{
            $cpapiemail =$owner->email;
            $cpapiusername = cpapi_helper::create_random_apiuser();
        }
        if($schoolname===false){
            $school->name= $owner->firstname . ' ' . $owner->lastname  .  ' ' .' school';
        }else{
            $school->name =$schoolname;
        }

        //create user
        $ret = cpapi_helper::create_cpapi_user(
            $owner->firstname ,
            $owner->lastname,
            $cpapiemail,
            $cpapiusername);
        if(!$ret){return false;}
        $ret=get_object_vars($ret);
        if(array_key_exists('apiusername',$ret)) {
            $school->apiuser = $ret['apiusername'];
            $school->apisecret = $ret['apisecret'];
            $id = $DB->insert_record(constants::M_TABLE_SCHOOLS, $school);
            if ($id) {
                $school->id = $id;
                return $school;
            } else {
                return false;
            }
        }else{
            return false;
        }
    }
}//end of class
