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
$type    = optional_param('type', 'plan', PARAM_TEXT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

require_login();

$category = null;
$baseurl = constants::M_URL;



$context = context_system::instance();
require_capability('block/poodllclassroom:manageintegration', $context);

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
        case 'school':
            if ($confirm and confirm_sesskey()) {
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
            $message = get_string('deleteschoolconfirm', constants::M_COMP);
            echo $renderer->confirm($message, $yesurl, $returnurl);
            echo $renderer->footer();
            die;
    }

}


switch($type){
    case 'plan':
        $editform = new \block_poodllclassroom\local\form\editplanform();
        break;
    case 'school':
        $editform = new \block_poodllclassroom\local\form\editschoolform();
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
                $updatedata = array('id'=>$data->id,'name'=>$data->name,
                        'maxusers'=>$data->maxusers,
                        'maxcourses'=>$data->maxcourses,
                        'features'=>$data->features,
                        'billinginterval'=>$data->billinginterval,
                        'price'=>$data->price,
                        'description'=>$data->description,
                        'upstreamplan'=>$data->upstreamplan,
                        'timemodified'=>time());
                $result=$DB->update_record(constants::M_TABLE_PLANS,$updatedata);
            }
            break;
        case 'school':
            $updatedata = array('id'=>$data->id,'planid'=>$data->planid);
            $result=$DB->update_record(constants::M_TABLE_SCHOOLS,$updatedata);
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
    case 'school':
        $usedata = $DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$id));
        $editform->set_data($usedata);
}

$strheading = get_string('subsschoolsplans', constants::M_COMP);
$PAGE->set_title($strheading);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strheading);


echo $renderer->header();
echo $renderer->heading($strheading);
echo $editform->display();
echo $renderer->footer();

