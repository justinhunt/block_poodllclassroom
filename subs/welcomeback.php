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

//There is a hosted page bug. So if they get here lets just run the retrieve events thingy and move on:
chargebee_helper::retrieve_process_events();
redirect($CFG->wwwroot . '/my/');
// nothing beyond here will currently happen ....


$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', constants::M_COMP));
$PAGE->navbar->add(get_string('pluginname', constants::M_COMP));

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
           $ret = common::create_poodll_sub( $subscription,$currency_code,$amount_paid,$subscription->customer_id);
           if(!$ret){
               $ret = "something was not right with that school ...";
           }

        //if its an existing sub ... update it
        }else{
            //WHAT TO DO HERE??? If they downgrade till next time? upgrade?
            //IN the interests of getting this out the door. Lets hide the changesub link for now
            //do some update with $poodllsub
            $ret = "That was an update, not sure about that";
        }
        $ret = "That worked";

    }else{
        $ret = "rubbish hosted page";
    }

}else{
    $ret = "nah no good. state was ...: " . $state;
}



//get our renderer
$renderer = $PAGE->get_renderer(constants::M_COMP);

echo $renderer->header();
echo $renderer->heading($companyname);
echo $ret;
echo $renderer->footer();