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

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/sync.php',array());
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
echo $renderer->header();
echo $renderer->heading( get_string('syncpage', constants::M_COMP),2);

if(!$ok) {
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

        }else{

        }


        break;
    case "subs":
        $syncsubform = new \block_poodllclassroom\local\form\syncsubform();
        break;
}



echo $renderer->footer();