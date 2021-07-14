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
$type    = optional_param('type', 'myschool', PARAM_TEXT);//school //myschool
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

require_login();

$category = null;
$baseurl = constants::M_URL;



$context = context_system::instance();
//require_capability('block/poodllclassroom:managepoodllclassroom', $context);

if (!empty($returnurl)) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = new moodle_url($CFG->wwwroot . '/my', array());
}


$PAGE->set_context($context);
$baseurl = new moodle_url($baseurl . '/subs/editmyschool.php', array('id' => $id, 'type'=>$type));
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$renderer = $PAGE->get_renderer(constants::M_COMP);

$ok =false;
$school=false;
if($id>0) {
    $school = $DB->get_record(constants::M_TABLE_SCHOOLS, array('id' => $id));
}
if($school){
    $ok=true;
}else{
    redirect($returnurl,get_string('donthaveaschool',constants::M_COMP),
        3,\core\output\notification::NOTIFY_WARNING);
}
if($ok){
    $ok = $school->ownerid==$USER->id;
    if(!$ok){
        $reseller = common::fetch_me_reseller();
        if($reseller){
            $resold_schools= common::fetch_schools_by_reseller($reseller->id);
            foreach($resold_schools as $resold_school){
                if($school->id==$resold_school->id){
                    $ok=true;
                    break;
                }
            }
        }
    }
}
if(!$ok){
    //we dont have ownership of this school so cancel out of here
    $returnurl=$CFG->wwwroot . '/my';
    redirect($returnurl,get_string('dontownthisschool',constants::M_COMP),
            3,\core\output\notification::NOTIFY_WARNING);
}


if ($delete && $id) {
    $PAGE->url->param('delete', 1);
    switch($type){
        case 'myschool':
            //there is no delete myschool (yet!!)
    }

}


switch($type){
    case 'myschool':
        $editform = new \block_poodllclassroom\local\form\editmyschoolform();
}


if ($editform->is_cancelled()){
    redirect($returnurl);
}else if($data = $editform->get_data()) {
    switch($type){

        case 'myschool':
            $theschool = $DB->get_record(constants::M_TABLE_SCHOOLS, array('id' => $data->id));
            if($theschool && $theschool->ownerid==$USER->id && !empty($data->name)) {
                if(!empty($data->siteurl)){$data->siteurls=json_encode($data->siteurl);}
                $DB->update_record(constants::M_TABLE_SCHOOLS,$data);
                //common::set_schoolname($theschool, $data->name);
            }
    }


    // Redirect to where we were before.
    redirect($returnurl);

}

switch($type){

    case 'myschool':

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
echo $editform->display();
echo $renderer->footer();

