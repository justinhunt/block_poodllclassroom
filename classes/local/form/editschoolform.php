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



class editschoolform extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'typeheading', get_string('editschoolsub', constants::M_COMP));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $companies = common::fetch_companies_array();
        $mform->addElement('select', 'companyid', get_string('company', constants::M_COMP), $companies);

        $owners = common::fetch_owners_array();
        $mform->addElement('select', 'ownerid', get_string('owner', constants::M_COMP), $owners);

        $mform->addElement('text', 'upstreamsubid', get_string('upstreamsub', constants::M_COMP), array('size'=>70));
        $mform->setType('upstreamsubid', PARAM_TEXT);
        $mform->setDefault('upstreamsubid', 'unspecified');

        $mform->addElement('text', 'upstreamownerid', get_string('upstreamowner', constants::M_COMP), array('size'=>70));
        $mform->setType('upstreamownerid', PARAM_TEXT);
        $mform->setDefault('upstreamownerid', 'unspecified');

        $mform->addElement('hidden', 'type','school');
        $mform->setType('type', PARAM_TEXT);

        $options = common::fetch_plans_array();
        $mform->addElement('select', 'planid', get_string('plan', constants::M_COMP), $options);


        //add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('save', constants::M_COMP));

    }
}