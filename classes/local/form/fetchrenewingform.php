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
 * A form to fetch criteria to use for fetching info for report
 *
 * @copyright 2020 Justin Hunt (poodllsupport@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   block_poodllclassroom
 */


namespace block_poodllclassroom\local\form;

use \block_poodllclassroom\constants;
use \block_poodllclassroom\common;

require_once($CFG->dirroot.'/lib/formslib.php');

class fetchrenewingform extends \moodleform {

    public function definition()
    {
        $mform = $this->_form;

        $mform->addElement('header', 'fetchrenewingheading', get_string('fetchrenewingform', constants::M_COMP));
        $mform->addElement('date_selector', 'renewingfrom', get_string('renewingfrom', constants::M_COMP));
        $mform->addElement('date_selector', 'renewingto', get_string('renewingto', constants::M_COMP));


        $mform->addElement('hidden', 'type', 'fetchrenewing');
        $mform->setType('type', PARAM_TEXT);
        //add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('save', constants::M_COMP));

    }
}