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

    public function definition()
    {
        $mform = $this->_form;

        $superadmin = $this->_customdata['superadmin'];

        $mform->addElement('header', 'typeheading', get_string('editschool', constants::M_COMP));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text', 'name', get_string('schoolname', constants::M_COMP), array('size' => 70));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', '-- ?? --');

        if ($superadmin){
            $mform->addElement('text', 'apiuser', get_string('apiuser', constants::M_COMP), array('size' => 70));
            $mform->setType('apiuser', PARAM_TEXT);

            $mform->addElement('text', 'apisecret', get_string('apisecret', constants::M_COMP), array('size' => 70));
            $mform->setType('apisecret', PARAM_TEXT);

            $resellers = common::fetch_resellers_array();
            $mform->addElement('select', 'resellerid', get_string('reseller', constants::M_COMP), $resellers);

            $owners = common::fetch_owners_array();
            $mform->addElement('select', 'ownerid', get_string('owner', constants::M_COMP), $owners);

            $mform->addElement('text', 'upstreamownerid', get_string('upstreamowner', constants::M_COMP), array('size' => 70));
            $mform->setType('upstreamownerid', PARAM_TEXT);
            $mform->setDefault('upstreamownerid', 'unspecified');

            $mform->addElement('textarea', 'jsonfields', get_string('jsonfields', constants::M_COMP), array('size'=>70));
            $mform->setType('jsonfields', PARAM_RAW);
            $mform->setDefault('jsonfields', '{}');
        }

        for($urlcount =0; $urlcount<5; $urlcount++) {
            $mform->addElement('text', 'siteurl[' . $urlcount  . ']', get_string('siteurl', constants::M_COMP, $urlcount+1), array('size' => 70));
            $mform->setType('siteurl[' . $urlcount  . ']', PARAM_TEXT);
        }

        $mform->addElement('text', 'status', get_string('status', constants::M_COMP), array('size'=>70));
        $mform->setType('status', PARAM_TEXT);
        $mform->setDefault('status', '-');

        $mform->addElement('hidden', 'type','school');
        $mform->setType('type', PARAM_TEXT);



        //add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('save', constants::M_COMP));

    }
}