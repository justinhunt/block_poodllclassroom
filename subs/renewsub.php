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
require('../../../config.php');

$subid        = required_param('subid',  PARAM_INT);

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/renewsub.php',array('subid'=>$subid));
$course = get_course(1);
require_login($course);


$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', constants::M_COMP));
$PAGE->navbar->add(get_string('pluginname', constants::M_COMP));
//get our renderer
$renderer = $PAGE->get_renderer(constants::M_COMP);


//Get the sub
$sub =$DB->get_record(constants::M_TABLE_SUBS,array('id'=>$subid));
$extended_sub=common::get_extended_sub_data([$sub])[0];

//$ok = has_capability('block/poodllclassroom:managepoodllclassroom', $context);
$ok = ($extended_sub->school->ownerid == $USER->id) ||($extended_sub->school->reseller->userid == $USER->id) ;
if(!$ok){
    echo $renderer->header();
    echo $renderer->heading($SITE->fullname);
    echo  get_string('nopermission', constants::M_COMP);
    echo $renderer->footer();
    die;
}

//get subscription details
$errormessage = "unknown error";
if($extended_sub) {
    $result = \block_poodllclassroom\chargebee_helper::bill_next_renewal_of_sub($extended_sub->upstreamsubid);
    if($result) {
        $paynow_hostedpage = \block_poodllclassroom\chargebee_helper::get_pay_outstanding($extended_sub->school->upstreamownerid);
        if ($paynow_hostedpage && $paynow_hostedpage['success']) {
            $paynowpage_url = $paynow_hostedpage['payload']->hosted_page->url;
            redirect($paynowpage_url, get_string('renewedsuccessfully_forwarding', constants::M_COMP));
        }else{
            $errormessage = get_string('renewed_but_no_paynow', constants::M_COMP);
        }
    }else{
        $errormessage = get_string('unabletorenew', constants::M_COMP);
    }
}else{
    $errormessage = "Unable to renew that sub for some reason. Please contact Poodll.";
}

//if we get to here there was an issue and user could not be sent to portal
echo $renderer->header();
echo $renderer->heading($SITE->fullname);
echo  get_string('couldnotsendtoportal', constants::M_COMP);
echo $renderer->footer();