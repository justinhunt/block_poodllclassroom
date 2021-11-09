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
use block_poodllclassroom\cpapi_helper;
require('../../../config.php');

$id = optional_param('id', 0, PARAM_INT);
$type=optional_param('type', 'school', PARAM_TEXT);
$returnurl=optional_param('returnurl', '', PARAM_TEXT);

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/schooldetails.php',array('id'=>$id));
$course = get_course(1);
require_login($course);


$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', constants::M_COMP));
$PAGE->navbar->add(get_string('pluginname', constants::M_COMP));

//get the school if its truly yours, if not it never was ...
$school = common::get_resold_or_my_school($id);

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
$subs =    common::fetch_subs_by_school($school->id); //common::fetch_schoolsubs_by_school($school->id);
$extended_subs = common::get_extended_sub_data($subs);
$display_subs = common::get_display_sub_data($extended_subs);
$subssectiondata = array('subs'=>array_values($display_subs));
if(count($subssectiondata['subs'])<1){
    $subssectiondata['nosubs']=true;
}
$subssectiondata['show_expiretime']=true;
$subssectiondata['show_payment']=true;
$subssectiondata['show_status']=true;

$checkouturl  = new \moodle_url(constants::M_URL . '/subs/checkout.php',array());
$checkoutbuttondata = ['school'=>$school,'subtype'=>'all', 'checkouturl'=>$checkouturl->out()];
$checkoutbutton = $renderer->render_from_template('block_poodllclassroom/checkoutpagebutton', $checkoutbuttondata);
$subssectiondata['checkoutbutton']=$checkoutbutton ;

//return the page header
echo $renderer->header();


if(true) {

    //here we set up any info we need to pass into javascript
    $opts =Array();
    $opts['siteprefix']= get_config(constants::M_COMP,'chargebeesiteprefix');
    $opts['changeplanclass']=constants::M_COMP . '_changeplan';
    $opts['gocbcheckoutclass']=constants::M_COMP . '_gocbcheckout';
    $opts['gocbmanageclass']=constants::M_COMP . '_subsmanagelink';
    $PAGE->requires->js_call_amd(constants::M_COMP . "/chargebeehelper", 'init', array($opts));
    //clipboard copy thingy
    $clipboardopts = array();
    $PAGE->requires->js_call_amd(constants::M_COMP . "/clipboardhelper", 'init', array($clipboardopts));

    $content = $renderer->render_from_template('block_poodllclassroom/schoolheader',$school);
    $content .='<br>';
    $content .= $renderer->render_from_template('block_poodllclassroom/subsheader',$subssectiondata);

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
    $moodledata = ['school'=>$school,'subs'=>$moodlesubs,
        'planfamily'=>'all',
        'platform'=>constants::M_PLATFORM_MOODLE,
        'checkouturl'=>$checkouturl->out(),
        'editschoolurl'=>false];
    if(count($moodlesubs)>0){
        $moodledata['hassubs']=true;
        //schoolname
        $moodledata['school']=$moodlesubs[0]->school;

        //edit url
        $urlparams= array('id' => $moodlesubs[0]->school->id,'type'=>'school','returnurl' => $PAGE->url->out_as_local_url());
        $theurl = new \moodle_url(constants::M_URL . '/subs/editmyschool.php', $urlparams);
        $moodledata['editschoolurl'] = $theurl->out();

        //usage data
        $schoolusagedata = cpapi_helper::fetch_usage_data($moodlesubs[0]->school->apiuser);
        if ($schoolusagedata) {
            $moodledata['usagereport'] = $renderer->display_usage_report($schoolusagedata);
        } else {
            $moodledata['usagereport'] = get_string('nousagedata', constants::M_COMP);
        }
    };
    $content .= $renderer->render_from_template('block_poodllclassroom/moodlesubs',
            $moodledata);


    //Platform LTI Section
    $ltidata=['school'=>$school,'subs'=>$ltisubs,
        'planfamily'=>'all',
        'platform'=>constants::M_PLATFORM_LTI,
        'checkouturl'=>$checkouturl->out()];
    if(count($ltisubs)>0){
        $ltidata['hassubs']=true;
        //schoolname
        $ltidata['school']=$ltisubs[0]->school;
    }
    $content .= $renderer->render_from_template('block_poodllclassroom/ltisubs',
        $ltidata);

    //Platform Classroom Section
    if(count($classroomsubs)>0){
        $classroomdata= ['school'=>$school,'subs'=>$classroomsubs,
            'planfamily'=>'all',
            'platform'=>constants::M_PLATFORM_CLASSROOM,
            'checkouturl'=>$checkouturl->out()];
        if(count($classroomsubs)>0){
            $classroomdata['hassubs']=true;
            //schoolname
            $classroomdata['school']=$classroomsubs[0]->school;
        }
        $content .= $renderer->render_from_template('block_poodllclassroom/classroomsubs',
           $classroomdata);
    }

    //return button
    $thebutton = new \single_button(
        new \moodle_url($CFG->wwwroot . $returnurl,array()),
        get_string('back', constants::M_COMP), 'get');
    $content .= $renderer->render($thebutton);

    echo $content;


}else{
    echo  get_string('nopermission', constants::M_COMP);
}

echo $renderer->footer();