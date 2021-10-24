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



class editresellerform extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'typeheading', get_string('editreseller', constants::M_COMP));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $resellertypes = [constants::M_RESELLER_THIRDPARTY=>'Third Party',constants::M_RESELLER_POODLL=>'Poodll'];
        $mform->addElement('select', 'resellertype', get_string('resellertype', constants::M_COMP), $resellertypes);

        $mform->addElement('text', 'name', get_string('resellername', constants::M_COMP), array('size'=>70));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', "you need to enter a reseller name", 'required', null, 'client');

        $users = common::fetch_users_array();
        $mform->addElement('select', 'userid', get_string('users', constants::M_COMP), $users);

        $mform->addElement('text', 'description', get_string('description', constants::M_COMP), array('size'=>70));
        $mform->setType('description', PARAM_TEXT);
        $mform->setDefault('description', '');

        $mform->addElement('textarea', 'jsonfields', get_string('jsonfields', constants::M_COMP), array('size'=>70));
        $mform->setType('jsonfields', PARAM_RAW);
        $mform->setDefault('jsonfields', '{}');

        $mform->addElement('text', 'status', get_string('status', constants::M_COMP), array('size'=>70));
        $mform->setType('status', PARAM_TEXT);
        $mform->setDefault('status', '-');

        $mform->addElement('hidden', 'type','reseller');
        $mform->setType('type', PARAM_TEXT);


        //add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('save', constants::M_COMP));

    }
}