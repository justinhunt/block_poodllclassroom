<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/16
 * Time: 19:31
 */

namespace block_poodllclassroom;

defined('MOODLE_INTERNAL') || die();

use \block_poodllclassroom\common;
use \block_poodllclassroom\constants;


class iomadadaptor
{

    public static function cancel_sub($params) {
        global $CFG, $SESSION, $DB, $USER;

        //initialise return value
        $ret = [];
        $ret['error'] = false;
        $ret['message'] = '';


        //Get Poodll School and once its got, cancel it
        $poodllschool =common::get_poodllschool_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::cancel_poodllschool($poodllschool);
            $ret['message'] = 'Subscription cancelled ok';
        }else{
            $ret['error'] = true;
            $ret['message'] = 'No such subscription was found';
        }
        return $ret;
    }


    public static function resume_sub($params) {
        global $CFG, $SESSION, $DB, $USER;


        //initialise return value
        $ret = [];
        $ret['error'] = false;
        $ret['message'] = '';

        //Get Poodll School and once its got, resume it
        $poodllschool =common::get_poodllschool_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::resume_poodllschool($poodllschool);
            $ret['message'] = 'Subscription resumed ok';
        }else{
            $ret['error'] = true;
            $ret['message'] = 'No such Subscription  was found';
        }
        return $ret;
    }

    public static function pause_sub($params) {
        global $CFG, $SESSION, $DB, $USER;


        //initialise return value
        $ret = [];
        $ret['error'] = false;
        $ret['message'] = '';

        //Get Poodll School and once its got, pause it
        $poodllschool =common::get_poodllschool_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::pause_poodllschool($poodllschool);
            $ret['message'] = 'Subscription paused ok';
        }else{
            $ret['error'] = true;
            $ret['message'] = 'No such Subscription  was found';
        }
        return $ret;

    }


    public static function update_sub($params) {
        global $CFG, $SESSION, $DB, $USER;

        //initialise return value
        $ret = [];
        $ret['error'] = false;
        $ret['message'] = '';

        //Get Poodll School and once its got, update it
        $poodllschool =common::get_poodllschool_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            if($poodllschool->upstreamplanid != $params['upstreamplanid']) {
                common::update_poodllschool_from_upstream($poodllschool, $params['upstreamplanid']);
                $ret['message'] = 'Subscription  updated successfully';
            }else{
                $ret['message']= 'nothing to update. all good.';
            }
        }else{
            $ret['error'] = true;
            $ret['message'] = '';
        }
        return $ret;
    }

    public static function create_sub($params)
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


        //if we alredy have this poodllschol then we just need to update to the new plan
        //Its unclear yet if that will happen here in this call, or another one
        $poodllschool =common::get_poodllschool_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::update_poodllschool_from_upstream($poodllschool, $params['upstreamplanid']);
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
        $parentlevel = \company::get_company_parentnode($thecompany->id);
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
            \company::upsert_company_user($theuser->id, $thecompany->id, $departmentid,  $userdata['managertype'], $userdata['educator']);
            $newuserid=$theuser->id;
            $newusername=$theuser->username;
        }

        //we have created a company and possibly a user also
        //at this point we should update the poodllclassroom tables also
        $plan = common::fetch_poodllplan_from_upstreamplan($params['upstreamplanid']);
        common::create_poodllschool($thecompany->id, $newuserid, $plan->id,
                $params['upstreamownerid'],$params['upstreamsubid']);

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

}