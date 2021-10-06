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
 * Manually re-process CB event
 *
 * @package    block_poodllclassroom
 * @copyright  2019 Justin Hunt  {@link http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_poodllclassroom\constants;
use block_poodllclassroom\common;
use block_poodllclassroom\chargebee_helper;
require('../../../config.php');

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if($returnurl==''){$returnurl=new moodle_url(constants::M_URL . '/subs/eventrunner.php',array());}

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/eventrunner.php',array());
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
    echo $renderer->heading( get_string('eventrunnerpage', constants::M_COMP),2);
    echo  get_string('nopermission', constants::M_COMP);
    echo $renderer->footer();
    return;
}

$eventrunnerform = new \block_poodllclassroom\local\form\eventrunnerform();
if ($eventrunnerform->is_cancelled()){
    redirect($returnurl);
}else if($data = $eventrunnerform->get_data()) {
  $ret =  chargebee_helper::retrieve_process_one_event($data->eventid,false);
  if($ret){
      redirect($returnurl, $ret);
  }else{
      redirect($returnurl, 'something probably went wrong');
  }
}else{
    echo $renderer->header();
    echo $renderer->heading( get_string('eventrunnerpage', constants::M_COMP),2);
    $eventrunnerform->display();
    echo $renderer->footer();
    return;
}


