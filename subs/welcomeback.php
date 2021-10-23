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

/**
 * Subs manage
 *
 * @package    block_poodllclassroom
 * @copyright  2019 Justin Hunt  {@link http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_poodllclassroom\constants;
use block_poodllclassroom\common;
use block_poodllclassroom\chargebee_helper;
require('../../../config.php');


$id        = optional_param('id','' , PARAM_TEXT);
$state       = optional_param('state', '', PARAM_TEXT);

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/welcomeback.php',array('id' => $id,'state'=>$state));
$course = get_course(1);
require_login($course);

//There was a hosted page bug. So if they got here we just ran the retrieve events thingy and move on:
//chargebee_helper::retrieve_process_events();
//redirect($CFG->wwwroot . '/my/');
// nothing beyond here will currently happen ....

/*
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', constants::M_COMP));
$PAGE->navbar->add(get_string('pluginname', constants::M_COMP));
*/

//company name
//Set the companyid
$companyname = $SITE->fullname;

if($state=='succeeded') {
    $hp = chargebee_helper::retrieve_hosted_page($id);
    if($hp){

        $subscription =$hp->hosted_page->content->subscription;

        //if its a new sub create it. If we already had it. Do not create it
        $poodllsub=common::get_poodllsub_by_upstreamsubid($subscription->id);
        if($poodllsub===false) {
            $currency_code= $subscription->currency_code;
            $amount_paid= $subscription->subscription_items[0]->amount;
            //if we have a passed in school id we can link the sub to the school.
            //if we do not then it will try to guess it from the owner, but resellers with multiple schools will fail
            $pass_thru_content=$hp->hosted_page->pass_thru_content;
            $downstreamschoolid = false;
            if($pass_thru_content && !empty($pass_thru_content)){
                $pass_thru_array=json_decode($pass_thru_content);
                if(array_key_exists("schoolid",$pass_thru_array )){
                    $downstreamschoolid = $pass_thru_array['schoolid'];
                }
            }
            $downstreamschoolid = $hp->hosted_page->content->subscription;
           $ret = common::create_poodll_sub( $subscription,$currency_code,$amount_paid,$subscription->customer_id,$downstreamschoolid);
           if(!$ret){
               $ret = get_string('unabletocreatesub',constants::M_COMP);
               redirect($CFG->wwwroot . '/my/',$ret,3, \core\output\notification::NOTIFY_WARNING);
           }else{
               $ret = get_string('createdsub',constants::M_COMP);
               redirect($CFG->wwwroot . '/my/',$ret);
           }

        //if its an existing sub ... update it
        }else{
            // This should change upstream and here we pick ut up each time so not much to be done
            $ret = "Subscription Updated";
            redirect($CFG->wwwroot . '/my/',$ret);
        }
        $ret = get_string('createdsub',constants::M_COMP);
        redirect($CFG->wwwroot . '/my/',$ret);

    }else{
        $ret = get_string('unabletoverifysub',constants::M_COMP);
        redirect($CFG->wwwroot . '/my/',$ret,3, \core\output\notification::NOTIFY_WARNING);
    }

}else{
    $ret = get_string('unknowncbstatus',constants::M_COMP,$state);
    redirect($CFG->wwwroot . '/my/',$ret,3, \core\output\notification::NOTIFY_WARNING);
}

redirect($CFG->wwwroot . '/my/',$ret);
