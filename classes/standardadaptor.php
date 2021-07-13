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


class standardadaptor
{

    public static function cancel_sub($params) {
        global $CFG, $SESSION, $DB, $USER;

        //initialise return value
        $ret = [];
        $ret['error'] = false;
        $ret['message'] = '';


        //Get Poodll School and once its got, cancel it
        $poodllschool =common::get_poodllsub_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::cancel_poodllsub($poodllschool);
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
        $poodllschool =common::get_poodllsub_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::resume_poodllsub($poodllschool);
            $ret['message'] = 'Subscription resumed ok';
        }else{
            $ret['error'] = true;
            $ret['message'] = 'No such Subscription  was found';
        }
        return $ret;
    }

    public static function reactivate_sub($params) {
        global $CFG, $SESSION, $DB, $USER;


        //initialise return value
        $ret = [];
        $ret['error'] = false;
        $ret['message'] = '';

        //Get Poodll School and once its got, resume it
        $poodllschool =common::get_poodllsub_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::reactivate_poodllsub($poodllschool);
            $ret['message'] = 'Subscription reactivated ok';
        }else{
            $ret['error'] = true;
            $ret['message'] = 'No such Subscription  was found';
        }
        return $ret;
    }

    public static function activate_sub($params) {
        global $CFG, $SESSION, $DB, $USER;


        //initialise return value
        $ret = [];
        $ret['error'] = false;
        $ret['message'] = '';

        //Get Poodll School and once its got, resume it
        $poodllschool =common::get_poodllsub_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::activate_poodllsub($poodllschool);
            $ret['message'] = 'Subscription activated ok';
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
        $poodllschool =common::get_poodllsub_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::pause_poodllsub($poodllschool);
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
        $poodllschool =common::get_poodllsub_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            if($poodllschool->upstreamplanid != $params['upstreamplanid']) {
                common::update_poodllsub_from_upstream($poodllschool, $params['upstreamplanid']);
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



        //if we alredy have this poodllschol then we just need to update to the new plan
        //Its unclear yet if that will happen here in this call, or another one
        $poodllschool =common::get_poodllsub_by_upstreamsubid($params['upstreamsubid']);
        if($poodllschool){
            common::update_poodllsub_from_upstream($poodllschool, $params['upstreamplanid']);
            $ret['schoolid'] = $poodllschool->id;
            $ret['userid'] = $poodllschool->userid;
            $theuser = $DB->get_record('user',array('id'=>$poodllschool->userid));
            if($theuser) {
                $ret['username'] = $theuser->username;
            }
            return $ret;
        }



        //we have created a school and possibly a user also
        //at this point we should update the poodllclassroom tables also
        $plan = common::fetch_poodllplan_from_upstreamplan($params['upstreamplanid']);


        $newuserid=1;
        $school = common::create_poodllsub($newuserid, $plan->id,
                $params['upstreamownerid'],$params['upstreamsubid']);

        if($newuserid) {
            $ret['schoolid'] = $school->id;
            $ret['userid'] = $newuserid;
            $ret['username'] = 'fake username';
        }else{
            $ret['error'] = true;
            $ret['message'] = "failed to create school AND user";
        }
        return $ret;
    }

}