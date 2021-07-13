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
 * Script to create a user for a particular company.
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



class editplanform extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'typeheading', get_string('addeditplan', constants::M_COMP));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'type','plan');
        $mform->setType('type', PARAM_TEXT);


        $mform->addElement('text', 'name', get_string('planname', constants::M_COMP), array('size'=>70));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('required'), 'required', null, 'client');

        $options = common::fetch_billingintervals();
        $mform->addElement('select', 'billinginterval', get_string('billinginterval', constants::M_COMP), $options);
        $mform->setType('billinginterval', PARAM_INT);

        $mform->addElement('text', 'maxusers', get_string('maxusers', constants::M_COMP), array());
        $mform->setType('maxusers', PARAM_INT);
        $mform->addRule('maxusers', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'maxcourses', get_string('maxcourses', constants::M_COMP), array());
        $mform->setType('maxcourses', PARAM_INT);
        $mform->addRule('maxcourses', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'features', get_string('features', constants::M_COMP), array());
        $mform->setType('features', PARAM_TEXT);

        $mform->addElement('text', 'upstreamplan', get_string('upstreamplan', constants::M_COMP), array());
        $mform->setType('upstreamplan', PARAM_TEXT);

        $mform->addElement('text', 'price', get_string('price', constants::M_COMP), array());
        $mform->setType('price', PARAM_TEXT);

        //plan families
        $planfamilies = common::fetch_planfamilies();
        $mform->addElement('select', 'planfamily', get_string('planfamily', constants::M_COMP), $planfamilies);
        $mform->setType('planfamily', PARAM_TEXT);

        //platform
        $platforms = common::fetch_platforms();
        $mform->addElement('select', 'platform', get_string('platform', constants::M_COMP), $platforms);
        $mform->setType('platform', PARAM_TEXT);

        $mform->addElement('text', 'description', get_string('description', constants::M_COMP), array());
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('textarea', 'jsonfields', get_string('jsonfields', constants::M_COMP), array('size'=>70));
        $mform->setType('jsonfields', PARAM_RAW);
        $mform->setDefault('jsonfields', '{}');


        //add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('save', constants::M_COMP));

    }
}