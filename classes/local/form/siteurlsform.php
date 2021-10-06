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
 * Script to edit a school
 */

/**
 * A form for the creation and editing of a user
 *
 * @copyright 2020 Justin Hunt (poodllsupport@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   block_poodllclassroom
 */


namespace block_poodllclassroom\local\form;

use \block_poodllclassroom\constants;
use \block_poodllclassroom\common;

require_once($CFG->dirroot.'/lib/formslib.php');



class siteurlsform extends \moodleform {

    public function definition()
    {
        $mform = $this->_form;

        $mform->addElement('header', 'siteurlsheading', get_string('siteurlsform', constants::M_COMP));

        $mform->addElement('text', 'upstreamschoolid', get_string('upstreamschoolid', constants::M_COMP), array('size' => 70));
        $mform->setType('upstreamschoolid', PARAM_TEXT);
        $mform->addElement('hidden', 'type', 'siteurls');
        $mform->setType('type', PARAM_TEXT);

        //add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('save', constants::M_COMP));

    }
}