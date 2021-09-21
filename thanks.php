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
 * Thanks for checking out
 *
 * @package block_poodllclassroom
 * @copyright  2020 Justin Hunt (http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
use block_poodllclassroom\constants;
$PAGE->set_context(\context_system::instance());
$PAGE->set_url(constants::M_URL . '/thanks.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
echo $OUTPUT->header();
$thanksdata = array('dashboardurl'=>$CFG->wwwroot . '/my/');
echo $OUTPUT->render_from_template('block_poodllclassroom/thanks',$thanksdata );
echo $OUTPUT->footer();