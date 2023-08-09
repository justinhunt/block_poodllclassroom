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
 * Sync Subs and Schools
 *
 * @package    block_poodllclassroom
 * @copyright  2019 Justin Hunt  {@link http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_poodllclassroom\constants;
use block_poodllclassroom\common;
require('../../../config.php');


$type    = optional_param('type', 'schools', PARAM_TEXT);//schools //subs
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if($returnurl==''){$returnurl=new moodle_url(constants::M_URL . '/subs/sync.php',array('type'=>$type));}

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/sync.php',array('type'=>$type));
$course = get_course(1);
require_login($course);


//datatables css
//$PAGE->requires->css(new \moodle_url('https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'));

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', constants::M_COMP));
$PAGE->navbar->add(get_string('pluginname', constants::M_COMP));


$ok = has_capability('block/poodllclassroom:manageintegration', $context);


//get our renderer
$renderer = $PAGE->get_renderer(constants::M_COMP);


if(!$ok) {
    echo $renderer->header();
    echo $renderer->heading( get_string('syncpage', constants::M_COMP),2);
    echo  get_string('nopermission', constants::M_COMP);
    echo $renderer->footer();
    return;
}

switch($type) {
    case "schools":
        $syncschoolform = new \block_poodllclassroom\local\form\syncschoolform();
        if ($syncschoolform->is_cancelled()){
            redirect($returnurl);
        }else if($data = $syncschoolform->get_data()) {
          $ret =  common::create_school_from_upstreamid($data->upstreamschoolid);
          if(!$ret || !$ret['success']){
              redirect($returnurl, $ret["message"] );
          }else{
              redirect($returnurl, $ret["message"] );
          }
        }else{
            echo $renderer->header();
            echo $renderer->heading( get_string('syncpage', constants::M_COMP),2);
            $syncschoolform->display();
            echo "<br>----------------------------<br>";
            echo "<br>Push the doomsday button below to import all schools....<br>";
            //$allsync_buttons
            $allschoolsbutton = new \single_button(
                new \moodle_url(constants::M_URL . '/subs/sync.php',array('type'=>'allschools')),
                get_string('syncallschools', constants::M_COMP), 'get');
            echo "<br>----------------------------<br>";
            echo $renderer->render($allschoolsbutton);

            echo $renderer->footer();
            return;
        }
        break;
    case "allschools":
        $successmessages=[];
        $failmessages=[];

        echo $renderer->header();
        echo $renderer->heading( get_string('syncpage', constants::M_COMP),2);


        $allchargebeeusers = \block_poodllclassroom\chargebee_helper::fetch_allchargebee_userids();

        if($allchargebeeusers && count( $allchargebeeusers)>0){
            foreach($allchargebeeusers as $cbuserid){
                //fire stop while testing
                // if((count($successmessages) + count($failmessages)) > 5){ break;}

                $ret =  common::create_school_from_upstreamid($cbuserid);
                if($ret && $ret['success']){
                    $successmessages[] = $ret["message"];
                }else{
                    $failmessages[]  = $ret["message"];
                }

            }
        }



        echo "success=" . count($successmessages) . '<br>';
        echo "fail=" . count($failmessages) . '<br>';
        foreach($successmessages as $sm){
            echo $sm . '<br>';
        }
        foreach($failmessages as $fm){
            echo $fm . '<br>';
        }
        echo $renderer->footer();

        break;




    case "subs":
        $syncsubform = new \block_poodllclassroom\local\form\syncsubform();
        if ($syncsubform->is_cancelled()){
            redirect($returnurl);
        }else if($data = $syncsubform->get_data()) {
            if(empty($data->downstreamschoolid)) {
                $downstreamschoolid = false;
            }else{
                $downstreamschoolid = $data->downstreamschoolid;
            }
            $ret =  common::create_sub_from_upstreamid($data->upstreamsubid, $downstreamschoolid);
            if(!$ret || !$ret['success']){
                redirect($returnurl, $ret["message"] );
            }else{
                redirect($returnurl, $ret["message"] );
            }

        }else{
            echo $renderer->header();
            echo $renderer->heading( get_string('syncpage', constants::M_COMP),2);
            $syncsubform->display();
            echo "<br>----------------------------<br>";
            echo "<br>Push the doomsday button below to import all subscriptions....<br>";
            //$allsync_buttons
            $allsubsbutton = new \single_button(
                new \moodle_url(constants::M_URL . '/subs/sync.php',array('type'=>'allsubs')),
                get_string('syncallsubs', constants::M_COMP), 'get');
            echo "<br>----------------------------<br>";
            echo $renderer->render($allsubsbutton);

            echo $renderer->footer();
            return;
        }
        break;
    case "allsubs":
        $successmessages=[];
        $failmessages=[];

        echo $renderer->header();
        echo $renderer->heading( get_string('syncpage', constants::M_COMP),2);


        $allchargebeesubs = \block_poodllclassroom\chargebee_helper::fetch_allchargebee_subids();
        if($allchargebeesubs && count( $allchargebeesubs)>0){

            foreach($allchargebeesubs as $cbsubid){
                //fire stop while testing
               // if((count($successmessages) + count($failmessages)) > 5){ break;}

                $ret =  common::create_sub_from_upstreamid($cbsubid);
                if($ret && $ret['success']){
                    $successmessages[] = $ret["message"];
                }else{
                    $failmessages[]  = $ret["message"];
                }

            }
        }



        echo "success=" . count($successmessages) . '<br>';
        echo "fail=" . count($failmessages) . '<br>';

        foreach($successmessages as $sm){
            echo $sm . '<br>';
        }
        foreach($failmessages as $fm){
            echo $fm . '<br>';
        }
        echo $renderer->footer();

        break;

    case "removebogusschools":

            echo $renderer->header();
            echo $renderer->heading( get_string('removebogusschools', constants::M_COMP),2);

            echo "<br>Push the doomsday button below to remove all bogus schools....<br>";

            //list schools
        $schools = common::fetch_schools();
        foreach($schools as $school) {
            if ($school->upstreamownerid === 0) {
                echo $school->id . " " . $school->name . "<br>";
            }
        }

        //$allsync_buttons
        $removebogusschoolsbutton = new \single_button(
            new \moodle_url(constants::M_URL . '/subs/sync.php',array('type'=>'removeallbogusschools')),
            get_string('removeallbogusschools', constants::M_COMP), 'get');
        echo "<br>----------------------------<br>";
        echo $renderer->render($removebogusschoolsbutton);

        echo $renderer->footer();
        return;

    case "removeallbogusschools":
        $successmessages=[];
        $failmessages=[];

        echo $renderer->header();
        echo $renderer->heading( get_string('removeallbogusschools', constants::M_COMP),2);

        $schools = common::fetch_schools();
        foreach($schools as $school){
            if($school->upstreamownerid === 0){

                //cancel the deletion request if there exist subs using this plan
                $subs = common::fetch_subs_by_school($school->id);
                if($subs && count($subs)){
                    $failmessages[] = get_string('existingsubsforschool',constants::M_COMP) . ' - ' . $school->name;
                }else{
                   // $result=$DB->delete_records(constants::M_TABLE_SCHOOLS,array('id'=>$school->id));
                    $result=$DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$school->id));
                    if(!$result){
                        $failmessages[]  = 'FAILED deleting ' . $school->name;
                    }else{
                        $successmessages[] = 'would have deleted ' . $school->name;
                    }
                }


            }

        }


        echo "success=" . count($successmessages) . '<br>';
        echo "fail=" . count($failmessages) . '<br>';

        foreach($successmessages as $sm){
            echo $sm . '<br>';
        }
        foreach($failmessages as $fm){
            echo $fm . '<br>';
        }
        echo $renderer->footer();
}



