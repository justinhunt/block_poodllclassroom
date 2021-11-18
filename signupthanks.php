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
 * Signup Thanks
 *
 * @package    block_poodllclassroom
 * @subpackage block
 * @copyright  2021 onwards Justin Hunt  http://poodll.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_poodllclassroom\constants;
use block_poodllclassroom\common;
require('../../config.php');


$email    = optional_param('email', '', PARAM_EMAIL);//email of user who signed up
$planid = optional_param('planid', '', PARAM_TEXT);
$planfamily = optional_param('planfamily', '', PARAM_TEXT);

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/signupthanks.php',array('email'=>$email,'planid'=>$planid,'planfamily'=>$planfamily));

if(isloggedin()){
    redirect($CFG->wwwroot . '/my/');
}

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('signupthanks', constants::M_COMP));
$PAGE->navbar->add(get_string('thanks', constants::M_COMP));

//get our renderer
$renderer = $PAGE->get_renderer(constants::M_COMP);


echo $renderer->header();
echo $renderer->heading( get_string('signupthanks', constants::M_COMP),2);

$context = [
    'useremail'=>$email,
    'loginurl' => $CFG->wwwroot . '/login/index.php',
    'logintoken' => \core\session\manager::get_login_token()
];
echo $renderer->render_from_template(constants::M_COMP . '/signupthanks', $context);
echo $renderer->footer();

