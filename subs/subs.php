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

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/subs.php',array());
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
$resellers=common::fetch_resellers();
$plans=common::fetch_plans();
$schools=common::fetch_schools();
// $subs=common::fetch_subs_for_all_users();

//get our renderer
$renderer = $PAGE->get_renderer(constants::M_COMP);
echo $renderer->header();
echo $renderer->heading($SITE->fullname);


if($ok) {

    //plans
    $planstable = $renderer->fetch_plans_table($plans);
    echo $planstable;
    //set up datatables
    $planstableprops = new \stdClass();
    $planstableprops->deferRender=true;

    $p_opts = Array();
    $p_opts['tableid'] = constants::M_ID_PLANSTABLE;
    $p_opts['tableprops'] = $planstableprops;
    $PAGE->requires->js_call_amd(constants::M_COMP . "/datatables", 'init', array($p_opts));

    //resellers
    $resellerstable = $renderer->fetch_resellers_table($resellers);
    echo $resellerstable;
    //set up datatables
    $rtableprops = new \stdClass();
    $rtableprops->deferRender=true;

    $r_opts = Array();
    $r_opts['tableid'] = constants::M_ID_RESELLERTABLE;
    $r_opts['tableprops'] = $planstableprops;
    $PAGE->requires->js_call_amd(constants::M_COMP . "/datatables", 'init', array($r_opts));

    //schools
    $params=[];
    $returnurl = new \moodle_url(constants::M_URL . '/subs/subs.php', $params);
    $schoolstable = $renderer->fetch_schools_table($schools,$returnurl);
    echo $schoolstable;

    //set up datatables
    $schoolstableprops = new \stdClass();
    $schoolstableprops->deferRender=true;

    $s_opts = Array();
    $s_opts['tableid'] = constants::M_ID_SCHOOLSTABLE;
    $s_opts['tableprops'] = $schoolstableprops;
    $PAGE->requires->js_call_amd(constants::M_COMP . "/datatables", 'init', array($s_opts));


    //subs
   // $substable = $renderer->fetch_subs_table($subs);
   // echo $substable;

    //Sync Options
    $otherOptions = $renderer->fetch_other_options();
    echo $otherOptions;



}else{
    echo  get_string('nopermission', constants::M_COMP);
}

echo $renderer->footer();