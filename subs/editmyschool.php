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
 * poodll classroomrelated management functions
 *
 * @package    block_poodllclassroom
 * @copyright  2019 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require('../../../config.php');

use block_poodllclassroom\constants;
use block_poodllclassroom\common;
use block_poodllclassroom\cpapi_helper;
use block_poodllclassroom\chargebee_helper;

$id        = optional_param('id', 0, PARAM_INT);
$add        = optional_param('add', 0, PARAM_BOOL);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm    = optional_param('confirm', 0, PARAM_BOOL);
$type    = optional_param('type', 'school', PARAM_TEXT);//school //myschool
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

require_login();

$category = null;
$baseurl = constants::M_URL;



$context = context_system::instance();

if (!empty($returnurl)) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = new moodle_url($CFG->wwwroot . '/my/', array());
}


$PAGE->set_context($context);
$baseurl = new moodle_url($baseurl . '/subs/editmyschool.php', array('id' => $id, 'type'=>$type));
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$renderer = $PAGE->get_renderer(constants::M_COMP);
$reseller = common::fetch_me_reseller();

$ok =false;
$school=false;
if($id>0) {
    $school = $DB->get_record(constants::M_TABLE_SCHOOLS, array('id' => $id));
}
if($school){
    $ok=true;
}else{
    //if a reseller requested an add, then lets do that
    if($add && $reseller) {
        $school = common::create_blank_school(false, $reseller, $reseller->name . ' school');
        if($school){
            $backhereurl = new moodle_url($baseurl . '/subs/editmyschool.php', array('id' => $school->id, 'type'=>$type));
            redirect($backhereurl);
        }else {
            redirect($returnurl,get_string('could not create school', constants::M_COMP),
                3, \core\output\notification::NOTIFY_WARNING);
        }
    //else we do not have a valid school so we can edit it ya know
    }else{
        redirect($returnurl, get_string('donthaveaschool', constants::M_COMP),
            3, \core\output\notification::NOTIFY_WARNING);
    }
}
$caneditthisschool=false;
if($ok){
    $caneditthisschool = $school->ownerid==$USER->id;
    if(!$caneditthisschool){
        if($reseller){
            $resold_schools= common::fetch_schools_by_reseller($reseller->id);
            foreach($resold_schools as $resold_school){
                if($school->id==$resold_school->id){
                    $caneditthisschool=true;
                    break;
                }
            }
        }
    }
}
if(!$caneditthisschool){
    //we dont have ownership of this school so cancel out of here
    $returnurl=$CFG->wwwroot . '/my/';
    redirect($returnurl,get_string('dontownthisschool',constants::M_COMP),
            3,\core\output\notification::NOTIFY_WARNING);
}


if ($delete && $id) {
    $PAGE->url->param('delete', 1);
    switch($type){
        case 'school':

            //cancel the deletion request if there exist subs using this plan
            $subs = common::fetch_subs_by_school($id);
            if($subs && count($subs)){
                redirect($returnurl,get_string('existingsubsforschool',constants::M_COMP),
                    3,\core\output\notification::NOTIFY_WARNING);
            }

            if ($confirm and confirm_sesskey()) {
                //what to do about upstream???
                $result=$DB->delete_records(constants::M_TABLE_SCHOOLS,array('id'=>$id));
                redirect($returnurl);
            }

            //
            $strheading = get_string('deleteschool', constants::M_COMP);
            $PAGE->navbar->add($strheading);
            $PAGE->set_title($strheading);
            $PAGE->set_heading($SITE->fullname);
            echo $renderer->header();
            echo $renderer->heading($strheading);
            $yesurl = new moodle_url($baseurl . '/subs/editmyschool.php', array('id' => $id, 'delete' => 1,'type'=>'school',
                'confirm' => 1, 'sesskey' => sesskey(), 'returnurl' => $returnurl->out_as_local_url()));
            $message = get_string('deleteschoolconfirm', constants::M_COMP, $school->name);
            echo $renderer->confirm($message, $yesurl, $returnurl);
            echo $renderer->footer();
            die;
    }

}


switch($type){
    case 'school':
        $editform = new \block_poodllclassroom\local\form\editmyschoolform();
}


if ($editform->is_cancelled()){
    redirect($returnurl);
}else if($data = $editform->get_data()) {
    switch($type){

        case 'school':
            $theschool = $DB->get_record(constants::M_TABLE_SCHOOLS, array('id' => $data->id));
            if($theschool && !empty($data->name)) {
                if ($theschool->ownerid == $USER->id || $caneditthisschool) {

                    if (!empty($data->siteurl)) {
                        $data->siteurls = json_encode($data->siteurl);

                        $data->timemodified = time();
                        $DB->update_record(constants::M_TABLE_SCHOOLS, $data);
                        //update entry on cpapi too
                        $url1 = '';
                        $url2 = '';
                        $url3 = '';
                        $url4 = '';
                        $url5 = '';
                        list($url1, $url2, $url3, $url4, $url5) = $data->siteurl;
                        // \block_poodllclassroom\cpapi_helper::update_cpapi_sites($theschool->apiuser,$url1,$url2,$url3,$url4,$url5);
                        // \block_poodllclassroom\cpapi_helper::update_cpapi_sites($USER->username,$url1,$url2,$url3,$url4,$url5);

                        $cpapi_username = strtolower($theschool->apiuser);
                        cpapi_helper::update_cpapi_sites($cpapi_username, $url1, $url2, $url3, $url4, $url5);
                        cpapi_helper::update_cpapi_user($cpapi_username, $USER->firstname, $USER->lastname, $USER->email);
                        //update chargebee if required
                        if($theschool->name != $data->name) {
                            //a reseller will have multiple schools so we dont want to update the company name here, since it will be one of their clients name actually
                            if (!$reseller) {
                                chargebee_helper::update_chargebee_company($theschool->upstreamownerid, $data->name);
                            }
                            //update all the sub school ids
                            $subs = common::fetch_subs_by_school($id);
                            print_r($subs);
                            if ($subs) {
                                chargebee_helper::update_chargebee_subscription_schoolname(
                                        $data->name,
                                        $subs);
                            }
                            die;
                        }
                    }
                }
            }
    }


    // Redirect to where we were before.
    redirect($returnurl);
}

switch($type){

    case 'school':

            $usedata = new stdClass();
            $usedata->id=$school->id;
            $usedata->name=$school->name;


            //deal with URLS
            if(!empty($school->siteurls)) {
                $usedata->siteurl = json_decode($school->siteurls);
            }

            $editform->set_data($usedata);

}

$strheading = get_string('editmyschool', constants::M_COMP);
$PAGE->set_title($strheading);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strheading);


echo $renderer->header();
echo $renderer->heading($strheading);
$editform->display();
echo $renderer->footer();

