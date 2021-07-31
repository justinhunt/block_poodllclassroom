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

$id        = optional_param('id', 0, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$confirm    = optional_param('confirm', 0, PARAM_BOOL);
$type    = optional_param('type', 'plan', PARAM_TEXT);//plan/ sub school //myschool
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

require_login();

$category = null;
$baseurl = constants::M_URL;



$context = context_system::instance();
require_capability('block/poodllclassroom:manageintegration', $context);

$superadmin = has_capability('block/poodllclassroom:managepoodllclassroom', $context);

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
        $editform = new \block_poodllclassroom\local\form\editschoolform(null,['superadmin'=>$superadmin]);
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

            if (!$data->id) {
                $data->timemodified=time();
                $data->timecreated=time();
                //we were going to allow resellers to create and edit schools, and that is this super admin thing
                //we canned it. TODO remove from here and editschoolform
                if(!$superadmin){
                    $data->jsonfields='{}';
                    $data->apiuser='';
                    $data->apisecret='';
                    $reseller = common::fetch_me_reseller();
                    if($reseller) {
                        $data->ownerid = $reseller->userid;
                        $data->resellerid = $reseller->id;
                        $data->upstreamownerid = $reseller->upstreamuserid;
                    }else{
                        $data->ownerid = $USER->id;
                        $data->resellerid = common::fetch_poodll_resellerid();
                    }
                }
                if(empty($data->upstreamownerid) || strpos($data->upstreamownerid,'user-')===false){
                    $data->upstreamownerid = common::fetch_upstream_user_id($data->ownerid);
                }
                $result=$DB->insert_record(constants::M_TABLE_SCHOOLS,$data);
            } else {
                $data->timemodified=time();
                $result = $DB->update_record(constants::M_TABLE_SCHOOLS, $data);
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

