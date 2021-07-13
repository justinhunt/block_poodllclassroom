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

$id = optional_param('id', 0, PARAM_INT);

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/subs.php',array());
$course = get_course(1);
require_login($course);


$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', constants::M_COMP));
$PAGE->navbar->add(get_string('pluginname', constants::M_COMP));

//generally speaking the reseller is Poodll(=1),
$reseller = common::fetch_me_reseller();
$school=false;
if($reseller && $reseller->type == constants::RESELLER_THIRDPARTY) {
    $schools = common::fetch_schools_by_reseller($reseller->id);
    foreach ($schools as $aschool){
        if($aschool->id==$id){
            $school = $aschool;
        }
    }
}elseif($reseller && $reseller && $reseller->type == constants::RESELLER_POODLL){
    $school = $DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$id));

}else{
    $school=common::get_poodllschool_by_currentuser();
    if(!$school || $school->id != $id ){
        $school=false;
    }
}

//get our renderer
$renderer = $PAGE->get_renderer(constants::M_COMP);

if(!$school){
    //return the page header
    echo $renderer->header();
    echo $renderer->heading($SITE->fullname);
    echo  get_string('youcantaccessthatschool', constants::M_COMP);
    echo $renderer->footer();
    die;
}

//Subs Section
$subs = common::fetch_schoolsubs_by_school($school->id);
$extended_subs = common::get_extended_sub_data($subs);
$display_subs = common::get_display_sub_data($extended_subs);
$subssectiondata = array('subs'=>array_values($display_subs));


//return the page header
echo $renderer->header();
echo $renderer->heading($school->name);


if(true) {

    $content = $renderer->render_from_template('block_poodllclassroom/schoolheader',$school);
    $content = $renderer->render_from_template('block_poodllclassroom/subsheader',$subssectiondata);

    //Platform Subs Details Section
    $moodlesubs=[];
    $ltisubs=[];
    $classroomsubs=[];
    foreach ($display_subs as $dsub){

        switch($dsub->plan->platform){
            case constants::M_PLATFORM_MOODLE:
                $moodlesubs[] = $dsub;
                break;

            case constants::M_PLATFORM_LTI:
                $ltisubs[] = $dsub;
                break;
            case constants::M_PLATFORM_CLASSROOM:
                $classroomsubs[] = $dsub;
                break;
        }
    }

    //Platform Moodle Subs Section
    if(count($moodlesubs)>0){
        $content .= $renderer->render_from_template('block_poodllclassroom/moodlesubs',
                ['school'=>$moodlesubs[0]->school,'subs'=>$moodlesubs]);
    }

    //Platform LTI Section
    if(count($ltisubs)>0){
        $content .= $renderer->render_from_template('block_poodllclassroom/ltisubs',['subs'=>$ltisubs]);
    }

    //Platform Classroom Section
    if(count($classroomsubs)>0){
        $content .= $renderer->render_from_template('block_poodllclassroom/classroomsubs',['subs'=>$classroomsubs]);
    }

    echo $content;


}else{
    echo  get_string('nopermission', constants::M_COMP);
}

echo $renderer->footer();