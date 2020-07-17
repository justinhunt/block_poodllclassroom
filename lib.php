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

//require_once(dirname(__FILE__) . '/../../config.php'); // Creates $PAGE.

function block_poodllclassroom_output_fragment_mform($args) {
    global $CFG;


    require_once($CFG->dirroot . '/group/group_form.php');

    $args = (object) $args;
    $context = $args->context;
    $formname = $args->formname;
    $mform= null;
    $o = '';

    $formdata = [];
    if (!empty($args->jsonformdata)) {
        $serialiseddata = json_decode($args->jsonformdata);
        parse_str($serialiseddata, $formdata);
    }

    list($ignored, $course) = get_context_info_array($context->id);

    switch($formname){
        case 'creategroup':
            $group = new stdClass();
            $group->courseid = $course->id;

            require_capability('moodle/course:managegroups', $context);
            $editoroptions = [
                    'maxfiles' => EDITOR_UNLIMITED_FILES,
                    'maxbytes' => $course->maxbytes,
                    'trust' => false,
                    'context' => $context,
                    'noclean' => true,
                    'subdirs' => false
            ];
            $group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);

            $mform = new group_form(null, array('editoroptions' => $editoroptions), 'post', '', null, true, $formdata);
            // Used to set the courseid.
            $mform->set_data($group);

            if (!empty($args->jsonformdata)) {
                // If we were passed non-empty form data we want the mform to call validation functions and show errors.
                $mform->is_validated();
            }
            break;

        default:
    }


    if(!empty($mform)) {
        ob_start();
        $mform->display();
        $o .= ob_get_contents();
        ob_end_clean();
    }

    return $o;
}