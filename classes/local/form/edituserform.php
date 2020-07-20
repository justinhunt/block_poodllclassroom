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
 * Script to let a user create a course for a particular company.
 */

/**
 * A form for editing a user
 * @copyright 2020 Justin Hunt (poodllsupport@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   block_poodllclassroom
 */


namespace block_poodllclassroom\local\form;


use block_poodllclassroom\constants;
use block_poodllclassroom\common;

require_once($CFG->dirroot.'/lib/formslib.php');

class edituserform extends \moodleform {
    protected $title = '';
    protected $description = '';
    protected $selectedcompany = 0;
    protected $context = null;


    public function definition() {
        global $CFG;

        $mform =& $this->_form;
        $editoroptions = null;
        $filemanageroptions = null;

        if (!is_array($this->_customdata)) {
            throw new \coding_exception('invalid custom data for user_edit_form');
        }
        $editoroptions = $this->_customdata['editoroptions'];
        $filemanageroptions = $this->_customdata['filemanageroptions'];
        $user = $this->_customdata['user'];
        $userid = $user->id;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);


        // Then show the fields about where this block appears.
        $mform->addElement('header', 'header',
                            get_string('edituser', constants::M_COMP));

        // Shared fields.
        \useredit_shared_definition($mform, $editoroptions, $filemanageroptions, $user);


        // Add action buttons.
        //add the action buttons
        $this->add_action_buttons(get_string('cancel'), get_string('savechanges'));

    }


    // Perform some extra moodle validation.
    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);


        return $errors;
    }

}