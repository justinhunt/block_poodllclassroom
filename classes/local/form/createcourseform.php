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
 * A form for the creation and editing of course
 *
 * @copyright 2020 Justin Hunt (poodllsupport@gmail.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package   block_poodllclassroom
 */


namespace block_poodllclassroom\local\form;

require_once($CFG->dirroot.'/lib/formslib.php');

class createcourseform extends \moodleform {
    protected $title = '';
    protected $description = '';
    protected $selectedcompany = 0;
    protected $context = null;


    public function definition() {
        global $CFG;

        $mform =& $this->_form;
        $this->editoroptions = $this->_customdata['editoroptions'];
        $this->selectedcompany = $this->_customdata['companyid'];
        $this->context  = \context_coursecat::instance($CFG->defaultrequestcategory);

        $mform->addElement('hidden','id');

        // Then show the fields about where this block appears.
        $mform->addElement('header', 'header',
                            get_string('companycourse', 'block_iomad_company_admin'));

        $mform->addElement('text', 'fullname', get_string('fullnamecourse'),
                            'maxlength="254" size="50"');
        $mform->addHelpButton('fullname', 'fullnamecourse');
        $mform->addRule('fullname', get_string('missingfullname'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_MULTILANG);

        $mform->addElement('text', 'shortname', get_string('shortnamecourse'),
                            'maxlength="100" size="20"');
        $mform->addHelpButton('shortname', 'shortnamecourse');
        $mform->addRule('shortname', get_string('missingshortname'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_MULTILANG);

        // Create course as self enrolable.
        if (\iomad::has_capability('block/iomad_company_admin:edit_licenses', \context_system::instance())) {
            $selectarray = array(get_string('selfenrolled', 'block_iomad_company_admin'),
                                 get_string('enrolled', 'block_iomad_company_admin'),
                                 get_string('licensedcourse', 'block_iomad_company_admin'));
        } else {
            $selectarray = array(get_string('selfenrolled', 'block_iomad_company_admin'),
                                 get_string('enrolled', 'block_iomad_company_admin'));
        }
        $select = &$mform->addElement('select', 'selfenrol',
                            get_string('enrolcoursetype', 'block_iomad_company_admin'),
                            $selectarray);
        $mform->addHelpButton('selfenrol', 'enrolcourse', 'block_iomad_company_admin');
        $select->setSelected('no');

        $mform->addElement('editor', 'summary_editor',
                            get_string('coursesummary'), null, $this->editoroptions);
        $mform->addHelpButton('summary_editor', 'coursesummary');
        $mform->setType('summary_editor', PARAM_RAW);

        // Add action buttons.
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton',
                            get_string('createcourse', 'block_iomad_company_admin'));
        $buttonarray[] = &$mform->createElement('submit', 'submitandviewbutton',
                            get_string('createandvisitcourse', 'block_iomad_company_admin'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

    }

    public function get_data() {
        $data = parent::get_data();
        if ($data) {
            $data->title = '';
            $data->description = '';

            if ($this->title) {
                $data->title = $this->title;
            }

            if ($this->description) {
                $data->description = $this->description;
            }
        }
        return $data;
    }

    // Perform some extra moodle validation.
    public function validation($data, $files) {
        global $DB, $CFG;

        $errors = parent::validation($data, $files);
        if ($foundcourses = $DB->get_records('course', array('shortname' => $data['shortname']))) {
            if (!empty($data['id'])) {
                unset($foundcourses[$data['id']]);
            }
            if (!empty($foundcourses)) {
                foreach ($foundcourses as $foundcourse) {
                    $foundcoursenames[] = $foundcourse->fullname;
                }
                $foundcoursenamestring = implode(',', $foundcoursenames);
                $errors['shortname'] = get_string('shortnametaken', '', $foundcoursenamestring);
            }
        }

        return $errors;
    }

}