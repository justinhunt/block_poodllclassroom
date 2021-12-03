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
$type=optional_param('type', 'reseller', PARAM_TEXT);
$returnurl=optional_param('returnurl', '', PARAM_TEXT);

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/resellerdetails.php',array('id'=>$id));
$course = get_course(1);
require_login($course);


$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', constants::M_COMP));
$PAGE->navbar->add(get_string('pluginname', constants::M_COMP));

//get our renderer
$renderer = $PAGE->get_renderer(constants::M_COMP);

if(has_capability('block/poodllclassroom:manageintegration', $context)) {


    //if we are a reseller we are showing reseller things and reseller options
    $the_reseller = $DB->get_record(constants::M_TABLE_RESELLERS, array('id' => $id));

    if ($the_reseller) {
        //return the page header
        echo $renderer->header();

        $content = '';

        //fetch reseller header
        $resellerheader = $renderer->render_from_template('block_poodllclassroom/resellerheader', $the_reseller);
        $content .= $resellerheader;

        //display schools
        $resold_schools = common::fetch_schools_by_reseller($the_reseller->id);
        $resold_schools = common::add_expiring_sub_to_schools($resold_schools);
        $params = [];
        $returnurl = new \moodle_url(constants::M_URL . '/subs/subs.php', $params);
        $schoolstable = $renderer->fetch_schools_table($resold_schools, $returnurl);
        $content .= $schoolstable;

        //return button
        $thebutton = new \single_button(
            new \moodle_url($returnurl, array()),
            get_string('back', constants::M_COMP), 'get');
        $content .= $renderer->render($thebutton);

        echo $content;

        echo $renderer->footer();


    } else {
        redirect($CFG->wwwroot . '/my/', 'There is no such reseller');
    }
}else {
    redirect($CFG->wwwroot . '/my/', get_string('nopermission', constants::M_COMP));
}

