<?php


/**
 * External class.
 *
 * @package block_poollclassroom
 * @author  Justin Hunt - Poodll.com
 */

use \block_poodllclassroom\common;
use \block_poodllclassroom\constants;

class block_poodllclassroom_external extends external_api {


    public static function submit_mform_parameters() {
        return new external_function_parameters(
                array(
                        'contextid' => new external_value(PARAM_INT, 'The context id for the course'),
                        'jsonformdata' => new external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array'),
                        'formname' => new external_value(PARAM_TEXT, 'The formname')
                )
        );
    }

    public static function submit_mform($contextid,$jsonformdata, $formname) {
        global $CFG, $DB, $USER;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::submit_mform_parameters(),
                ['contextid' => $contextid, 'jsonformdata' => $jsonformdata, 'formname'=>$formname]);

        $context = context::instance_by_id($params['contextid'], MUST_EXIST);

        // We always must call validate_context in a webservice.
        self::validate_context($context);

        list($ignored, $course) = get_context_info_array($context->id);
        $serialiseddata = json_decode($params['jsonformdata']);

        $data = array();
        parse_str($serialiseddata, $data);

        switch($formname){
            case 'creategroup':
                    require_capability('moodle/course:managegroups', $context);
                    require_once($CFG->dirroot . '/group/lib.php');
                    require_once($CFG->dirroot . '/group/group_form.php');
                    $editoroptions = [
                            'maxfiles' => EDITOR_UNLIMITED_FILES,
                            'maxbytes' => $course->maxbytes,
                            'trust' => false,
                            'context' => $context,
                            'noclean' => true,
                            'subdirs' => false
                    ];
                    $group = new stdClass();
                    $group->courseid = $course->id;
                    $group = file_prepare_standard_editor($group, 'description', $editoroptions, $context, 'group', 'description', null);

                    // The last param is the ajax submitted data.
                    $mform = new group_form(null, array('editoroptions' => $editoroptions), 'post', '', null, true, $data);

                    $validateddata = $mform->get_data();

                    if ($validateddata) {
                        // Do the action.
                        $groupid = groups_create_group($validateddata, $mform, $editoroptions);
                    } else {
                        // Generate a warning.
                        throw new moodle_exception('erroreditgroup', 'group');
                    }

                    return $groupid;
                break;

        }





    }


    public static function submit_mform_returns() {
        return new external_value(PARAM_RAW);
        //return new external_value(PARAM_INT, 'group id');
    }

}
