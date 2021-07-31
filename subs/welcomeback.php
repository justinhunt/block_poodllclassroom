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
use block_poodllclassroom\chargebee;
require('../../../config.php');


$id        = required_param('id',  PARAM_TEXT);
$state       = required_param('state',  PARAM_TEXT);

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/welcomeback.php',array('id' => $id,'state'=>$state));
$course = get_course(1);
require_login($course);


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
    $hp = chargebee::retrieve_hosted_page($id);
    if($hp){
        $hpstring = json_encode($hp);
        $passthroughdata = json_decode($hp->hosted_page->pass_thru_content);
        $school = common::get_resold_or_my_school($passthroughdata->schoolid);

        $subscription =$hp->hosted_page->content->subscription;
        $paymentcurr = $subscription->currency_code;
        $payment = $subscription->subscription_items[0]->unit_price;
        $expiretime = $subscription->current_term_end;

        if($school) {
            $plan = common::get_plan($passthroughdata->planid);
            $billinginterval = $plan->billinginterval;

            //if its a new sub create it. If we already had it. Do not create it
            $poodllsub=common::get_poodllsub_by_upstreamsubid($subscription->id);
            if($poodllsub===false) {

                //this is where any sub specific stuff has to happen .. eg get LTI creds, or API user and secret
                $json_fields = common::process_new_sub($school, $plan, $subscription);

                common::create_poodllsub($school->id, $school->ownerid, $plan->id, $school->upstreamownerid,
                    $expiretime, $payment, $paymentcurr, $billinginterval,
                    $json_fields, $subscription,$hpstring);

            //if its an existing sub ... update it
            }else{
                //WHAT TO DO HERE??? If they downgrade till next time? upgrade?
                //IN the interests of getting this out the door. Lets hide the changesub link for now
                //do some update with $poodllsub
            }
            $ret = $hpstring;
        }else{
            $ret = "something was not right with that school ...";
        }
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