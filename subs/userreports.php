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
 * User Reports
 *
 * @package    block_poodllclassroom
 * @copyright  2019 Justin Hunt  {@link http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_poodllclassroom\constants;
use block_poodllclassroom\common;
require('../../../config.php');

$type    = optional_param('type', 'fetchrenewing', PARAM_TEXT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);


if (!empty($returnurl)) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = new moodle_url($CFG->wwwroot . '/my/', array());
}



//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/userreports.php',array());
$course = get_course(1);
require_login($course);


//datatables css
$PAGE->requires->css(new \moodle_url('https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'));

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', constants::M_COMP));
$PAGE->navbar->add(get_string('pluginname', constants::M_COMP));


$ok = has_capability('block/poodllclassroom:manageintegration', $context);
//$resellers=common::fetch_resellers();
//$plans=common::fetch_plans();
//$schools=common::fetch_schools();
// $subs=common::fetch_subs_for_all_users();

//get our renderer
$renderer = $PAGE->get_renderer(constants::M_COMP);
echo $renderer->header();
echo $renderer->heading($SITE->fullname);


if($ok) {


    switch($type){
        case 'fetchrenewing':
            $theform = new \block_poodllclassroom\local\form\fetchrenewingform();
            break;
    }

    if ($theform->is_cancelled()){

        redirect($returnurl);
    }else if($data = $theform->get_data()) {
        switch ($type) {

            case 'fetchrenewing':
                //get dates from form, call chargebee and get all the renewing subs
                $all_items=[];
                $ret = \block_poodllclassroom\chargebee_helper::list_subs_for_renewal($data->renewingfrom,$data->renewingto);
                if($ret) {
                    $subs_list  =$ret->list;
                    if($subs_list && count($subs_list)>0){
                        foreach($subs_list as $item){
                            $all_items[]=$item;
                        }
                    }
                    while ($subs_list && count($subs_list) > 0 && $ret->next_offset){
                        $ret = \block_poodllclassroom\chargebee_helper::list_subs_for_renewal($data->renewingfrom,$data->renewingto,$ret->next_offset);
                        $subs_list  =$ret->list;
                        if($subs_list && count($subs_list)>0){
                            foreach($subs_list as $item){
                                $all_items[]=$item;
                            }
                        }
                    }
                }

                //fetch upstream school ids with renewing subs
                $allschools = [];
                foreach($all_items as $one_item){
                    $sub = common::get_poodllsub_by_upstreamsubid($one_item->subscription->id);
                    if($sub) {
                        $school = common::get_school_by_sub($sub);
                        if ($school) {
                            $school->nextexpiry = $sub->next_billing_at;
                            $allschools[$school->id] = $school;
                        }
                    }

                }

                //call cpapi for usage stats on those schools
                $yearcol=3;
                $maxmonthcol=4;
                foreach ($allschools as $one_school){
                    $rawusagedata = \block_poodllclassroom\cpapi_helper::fetch_usage_data($one_school->apiuser);
                    $reportdata = \block_poodllclassroom\common::compile_report_data($rawusagedata);
                    $one_school->totalrecordings=$reportdata['record'][$yearcol]['value'];
                    $one_school->totalmins=$reportdata['recordmin'][$yearcol]['value'];
                    $one_school->maxmonthusers=$reportdata['pusers'][$maxmonthcol]['value'];
                }

                //display in datatables
                $params=[];
                $returnurl = new \moodle_url(constants::M_URL . '/subs/subs.php', $params);
                $renewerstable = $renderer->fetch_renewers_table($allschools,$returnurl);
                echo $renewerstable ;
                //set up datatables
                $renewerstableprops = new \stdClass();
                $renewerstableprops->deferRender=true;

                $r_opts = Array();
                $r_opts['tableid'] = constants::M_ID_PLANSTABLE;
                $r_opts['tableprops'] = $renewerstableprops;
                $PAGE->requires->js_call_amd(constants::M_COMP . "/datatables", 'init', array($r_opts));
                break;
        }
    }

    //display all the forms
    $fetchrenewing_form = new \block_poodllclassroom\local\form\fetchrenewingform();
    $fetchrenewing_form->display();


}else{
    echo  get_string('nopermission', constants::M_COMP);
}

echo $renderer->footer();