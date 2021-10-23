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
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm    = optional_param('confirm', 0, PARAM_BOOL);
$type    = optional_param('type', 'plan', PARAM_TEXT);//plan/ sub school //myschool
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

require_login();

$category = null;
$baseurl = constants::M_URL;



$context = context_system::instance();
require_capability('block/poodllclassroom:managepoodllclassroom', $context);

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = new moodle_url($baseurl . '/subs/subs.php', array());
}


$PAGE->set_context($context);
$baseurl = new moodle_url($baseurl . '/subs/edit.php', array('id' => $id, 'type'=>$type));
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$renderer = $PAGE->get_renderer(constants::M_COMP);

if ($delete && $id) {
    $PAGE->url->param('delete', 1);
    switch($type){
        case 'plan':
            if ($confirm and confirm_sesskey()) {
                $result=$DB->delete_records(constants::M_TABLE_PLANS,array('id'=>$id));
                redirect($returnurl);
            }

            //cancel the deletion request if their exist subs using this plan
            $subs = common::fetch_subs_by_plan($id);
            if($subs && count($subs)){
                redirect($returnurl,get_string('existingsubsforplan',constants::M_COMP),
                        3,\core\output\notification::NOTIFY_WARNING);
            }

            $strheading = get_string('deleteplan', constants::M_COMP);
            $PAGE->navbar->add($strheading);
            $PAGE->set_title($strheading);
            $PAGE->set_heading($SITE->fullname);
            echo $renderer->header();
            echo $renderer->heading($strheading);
            $yesurl = new moodle_url($baseurl . '/subs/edit.php', array('id' => $id, 'delete' => 1,'type'=>'plan',
                    'confirm' => 1, 'sesskey' => sesskey(), 'returnurl' => $returnurl->out_as_local_url()));
            $message = get_string('deleteplanconfirm', constants::M_COMP);
            echo $renderer->confirm($message, $yesurl, $returnurl);
            echo $renderer->footer();
            die;
        case 'sub':
            if ($confirm and confirm_sesskey()) {
                $result=$DB->delete_records(constants::M_TABLE_SUBS,array('id'=>$id));
                redirect($returnurl);
            }
            $strheading = get_string('deletesub', constants::M_COMP);
            $PAGE->navbar->add($strheading);
            $PAGE->set_title($strheading);
            $PAGE->set_heading($SITE->fullname);
            echo $renderer->header();
            echo $renderer->heading($strheading);
            $yesurl = new moodle_url($baseurl . '/subs/edit.php', array('id' => $id, 'delete' => 1,'type'=>'sub',
                    'confirm' => 1, 'sesskey' => sesskey(), 'returnurl' => $returnurl->out_as_local_url()));
            $message = get_string('deletesubconfirm', constants::M_COMP);
            echo $renderer->confirm($message, $yesurl, $returnurl);
            echo $renderer->footer();
            die;
        case 'school':

            //get the school
            $school = $DB->get_record(constants::M_TABLE_SCHOOLS, array('id' => $id));
            if(!$school){
                redirect($returnurl,get_string('badschool',constants::M_COMP),
                    3,\core\output\notification::NOTIFY_WARNING);
            }

            //cancel the deletion request if there exist subs using this plan
            $subs = common::fetch_subs_by_school($id);
            if($subs && count($subs)){
                redirect($returnurl,get_string('existingsubsforschool',constants::M_COMP),
                        3,\core\output\notification::NOTIFY_WARNING);
            }

            //only delete if its been confirmed
            if ($confirm and confirm_sesskey()) {
                //what to do about upstream???
                $result=$DB->delete_records(constants::M_TABLE_SCHOOLS,array('id'=>$id));
                redirect($returnurl);
            }

            $strheading = get_string('deleteschool', constants::M_COMP);
            $PAGE->navbar->add($strheading);
            $PAGE->set_title($strheading);
            $PAGE->set_heading($SITE->fullname);
            echo $renderer->header();
            echo $renderer->heading($strheading);
            $yesurl = new moodle_url($baseurl . '/subs/edit.php', array('id' => $id, 'delete' => 1,'type'=>'school',
                    'confirm' => 1, 'sesskey' => sesskey(), 'returnurl' => $returnurl->out_as_local_url()));
            $message = get_string('deleteschoolconfirm', constants::M_COMP,  $school->name);
            echo $renderer->confirm($message, $yesurl, $returnurl);
            echo $renderer->footer();
            die;
        case 'reseller':

            if ($confirm and confirm_sesskey()) {
                $result=$DB->delete_records(constants::M_TABLE_RESELLERS,array('id'=>$id));
                redirect($returnurl);
            }


            //cancel the deletion request if there exist schools under this reseller
            $schools = common::fetch_schools_by_reseller($id);
            if($schools && count($schools)){
                redirect($returnurl,get_string('existingschoolsforreseller',constants::M_COMP),
                        3,\core\output\notification::NOTIFY_WARNING);
            }

            $strheading = get_string('deletereseller', constants::M_COMP);
            $PAGE->navbar->add($strheading);
            $PAGE->set_title($strheading);
            $PAGE->set_heading($SITE->fullname);
            echo $renderer->header();
            echo $renderer->heading($strheading);
            $yesurl = new moodle_url($baseurl . '/subs/edit.php', array('id' => $id, 'delete' => 1,'type'=>'reseller',
                    'confirm' => 1, 'sesskey' => sesskey(), 'returnurl' => $returnurl->out_as_local_url()));
            $message = get_string('deleteresellerconfirm', constants::M_COMP);
            echo $renderer->confirm($message, $yesurl, $returnurl);
            echo $renderer->footer();
            die;
        case 'mysub':
            //there is no delete myschool (yet!!)
    }

}


switch($type){
    case 'plan':
        $editform = new \block_poodllclassroom\local\form\editplanform();
        break;
    case 'sub':
        $editform = new \block_poodllclassroom\local\form\editsubform();
        break;
    case 'school':
        $editform = new \block_poodllclassroom\local\form\editschoolform(null,['superadmin'=>true]);
        break;
    case 'reseller':
        $editform = new \block_poodllclassroom\local\form\editresellerform();
        break;
    case 'mysub':
        $editform = new \block_poodllclassroom\local\form\editmyschoolform();
}


if ($editform->is_cancelled()){
    redirect($returnurl);
}else if($data = $editform->get_data()) {

    switch($type){
        case 'plan':
            if (!$data->id) {
                $data->timemodified=time();
                $result=$DB->insert_record(constants::M_TABLE_PLANS,$data);
            } else {
                $data->timemodified=time();
                $result=$DB->update_record(constants::M_TABLE_PLANS,$data);
            }
            break;
        case 'sub':
            if (!$data->id) {
                $data->timemodified=time();
                $data->timecreated=time();
                $result=$DB->insert_record(constants::M_TABLE_SUBS,$data);
            } else {
                $data->timemodified=time();
                $result = $DB->update_record(constants::M_TABLE_SUBS, $data);
            }
            break;
        case 'reseller':
            if (!$data->id) {

                $reseller = $DB->get_record(constants::M_TABLE_RESELLERS,array('userid'=>$data->userid));
                if($reseller){
                    redirect($returnurl,get_string('oneuseronereseller',constants::M_COMP),
                        3,\core\output\notification::NOTIFY_WARNING);
                }

                $data->timemodified=time();
                $data->timecreated=time();
                $data->upstreamuserid=common::fetch_upstream_user_id($data->userid);
                $result=$DB->insert_record(constants::M_TABLE_RESELLERS,$data);
            } else {
                $reseller = $DB->get_record(constants::M_TABLE_RESELLERS,array('id'=>$data->id));
                if(!$reseller){
                    redirect($returnurl,get_string('cantupdatereseller',constants::M_COMP),
                        3,\core\output\notification::NOTIFY_WARNING);
                }
                if(common::fetch_schools_by_reseller($data->id) && $reseller->userid != $data->userid){
                    redirect($returnurl,get_string('cantchangereselleruserifschools',constants::M_COMP),
                        3,\core\output\notification::NOTIFY_WARNING);
                }

                $data->timemodified=time();
                $result = $DB->update_record(constants::M_TABLE_RESELLERS, $data);
            }
            break;

        case 'school':
            //deal with URLS
            if(!empty($data->siteurl)){$data->siteurls=json_encode($data->siteurl);}
            $reseller = common::fetch_me_reseller($data->ownerid);
            if (!$data->id) {
                if($reseller){
                    $school = common::create_blank_school($data->ownerid,$reseller,$data->name);
                }else{
                    $school = common::create_blank_school($data->ownerid, false,$data->name);
                }
                if(!empty($data->siteurl)){

                    $DB->update_record(constants::M_TABLE_SCHOOLS,$data);
                    $url1=''; $url2=''; $url3=''; $url4=''; $url5='';
                    list($url1,$url2,$url3,$url4,$url5) = $data->siteurl;
                    cpapi_helper::update_cpapi_sites($school->apiuser,$url1,$url2,$url3,$url4,$url5);
                }


            } else {
                $data->timemodified=time();
                if(!empty($data->siteurl)){$data->siteurls=json_encode($data->siteurl);}
                $data->timemodified=time();
                $oldschool = $DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$data->id));
                $result = $DB->update_record(constants::M_TABLE_SCHOOLS, $data);
                //update chargebee if required
                if($oldschool && $oldschool->name != $data->name) {
                    //only update company name if the user is not a reseller
                    if(!$reseller) {
                        chargebee_helper::update_chargebee_company($oldschool->upstreamownerid, $data->name);
                    }
                    //update all the sub school ids
                    $subs = common::fetch_subs_by_school($data->id);
                    //print_r($subs);
                    if ($subs) {
                        chargebee_helper::update_chargebee_subscription_schoolname(
                                $data->name,
                                $subs);
                    }
                }


                //update entry on cpapi too
                $url1=''; $url2=''; $url3=''; $url4=''; $url5='';
                list($url1,$url2,$url3,$url4,$url5) = $data->siteurl;
                cpapi_helper::update_cpapi_sites($data->apiuser,$url1,$url2,$url3,$url4,$url5);
                $owner =$DB->get_record('user', array('id' => $data->ownerid));
                $cpapi_username=strtolower($data->apiuser);
                cpapi_helper::update_cpapi_user($cpapi_username,$owner->firstname,$owner->lastname,$owner->email);
            }
            break;
        case 'mysub':
            $thesub = $DB->get_record(constants::M_TABLE_SUBS, array('id' => $data->id));
            if($thesub && $thesub->ownerid==$USER->id && !empty($data->schoolname)) {
                common::set_schoolname_by_sub($thesub, $data->schoolname);
            }
    }


    // Redirect to where we were before.
    redirect($returnurl);

}

switch($type){
    case 'plan':
        if ($id) {
            $usedata = $DB->get_record(constants::M_TABLE_PLANS,array('id'=>$id));
            $editform->set_data($usedata);
        }
        break;
    case 'sub':
        if ($id) {
            $usedata = $DB->get_record(constants::M_TABLE_SUBS, array('id' => $id));
            $editform->set_data($usedata);
        }
        break;
    case 'school':
        if ($id) {
            $usedata = $DB->get_record(constants::M_TABLE_SCHOOLS, array('id' => $id));
            //deal with URLS
            if(!empty($usedata->siteurls)) {
                $usedata->siteurl = json_decode($usedata->siteurls);
            }
            $editform->set_data($usedata);
        }
        break;
    case 'reseller':
        if ($id) {
            $usedata = $DB->get_record(constants::M_TABLE_RESELLERS,array('id'=>$id));
            $editform->set_data($usedata);
        }
        break;
    case 'mysub':
        $thesub = $DB->get_record(constants::M_TABLE_SUBS, array('id' => $id));
        if($thesub && $thesub->ownerid==$USER->id) {
            $usedata = new stdClass();
            $usedata->id=$thesub->id;
            $usedata->schoolname=common::get_schoolname_by_sub($thesub);
            $editform->set_data($usedata);
        }

}

$strheading = get_string('subsschoolsplans', constants::M_COMP);
$PAGE->set_title($strheading);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strheading);


echo $renderer->header();
echo $renderer->heading($strheading);
echo $editform->display();
echo $renderer->footer();

