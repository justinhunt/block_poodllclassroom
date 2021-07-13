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

use block_poodllclassroom\common;
use block_poodllclassroom\constants;

function block_poodllclassroom_output_fragment_mform($args) {
    global $CFG, $PAGE, $DB;

    $args = (object) $args;
    $context = $args->context;
    $formname = $args->formname;
    $itemid=$args->itemid;
    $mform= null;
    $o = '';



    list($ignored, $course) = get_context_info_array($context->id);

    switch($formname){

        case constants::FORM_ENROLUSER:
            $context = context_system::instance();


            // Set the companyid
            $schoolid = common::get_my_schoolid($context);
            $departmentid=0;
            $licenseid=0;
            $courseid=$itemid;
            $courses=[$courseid];
            $actionurl="unused";

            $data=null;
            $mform = new \block_poodllclassroom\local\form\enroluserform($actionurl,$context,$schoolid, $departmentid, $courses, $ajaxdata);

            break;

        case constants::FORM_CREATEUSER:
            $context = context_system::instance();


            // Set the companyid
            $schoolid = common::get_my_schoolid($context);
            $departmentid=0;
            $licenseid=0;

            $data=null;
            $mform = new \block_poodllclassroom\local\form\createuserform($schoolid, $departmentid,$licenseid, $data);
            if(isset($args->jsonformdata)){
                $data = json_decode($args->jsonformdata);
                $mform->set_data($data);
            }


            break;

        case constants::FORM_UPLOADUSER:
            $context = context_system::instance();

            // Set the companyid
            $schoolid = common::get_my_schoolid($context);


            $data=null;
            $mform = new \block_poodllclassroom\local\form\uploaduserform($schoolid, $data);
            if(isset($args->jsonformdata)){
                $data = json_decode($args->jsonformdata);
                $mform->set_data($data);
            }


            break;

        case constants::FORM_EDITUSER :
            $context = context_system::instance();

            require_once($CFG->dirroot . '/user/editlib.php');
            require_once($CFG->dirroot . '/user/profile/lib.php');

            // Set the companyid
            $schoolid = common::get_my_schoolid($context);
            $departmentid=0;
            $licenseid=0;
            $userid=$itemid;

            $user = $DB->get_record('user',array('id'=>$userid));

            $usercontext = context_user::instance($user->id);
            $editoroptions = array(
                'maxfiles'   => EDITOR_UNLIMITED_FILES,
                'maxbytes'   => $CFG->maxbytes,
                'trusttext'  => false,
                'forcehttps' => false,
                'context'    => $usercontext
            );

            $draftitemid = 0;
            $filemanagercontext = $editoroptions['context'];
            $filemanageroptions = array('maxbytes'       => $CFG->maxbytes,
                'subdirs'        => 0,
                'maxfiles'       => 1,
                'accepted_types' => 'web_image');

            $user = file_prepare_standard_editor($user, 'description', $editoroptions, $usercontext, 'user', 'profile', 0);
            file_prepare_draft_area($draftitemid, $filemanagercontext->id, 'user', 'newicon', 0, $filemanageroptions);
            $user->imagefile = $draftitemid;

            // Process email change cancellation.
            $cancelemailchange =false;
            if ($cancelemailchange) {
                cancel_email_update($user->id);
            }

            // Load user preferences.
            useredit_load_preferences($user);

            // Load custom profile fields data.
            profile_load_data($user);


            // Create form.
            $mform =new \block_poodllclassroom\local\form\edituserform(null, array('editoroptions' => $editoroptions,
                'schoolid' => $schoolid,
                'user' => $user,
                'filemanageroptions' => $filemanageroptions));
            //$mform = new \block_poodllclassroom\local\form\edituserform(null, array('filemanageroptions'=>$filemanageroptions,'editoroptions'=> $editoroptions, 'user'=>$user));

            $mform->set_data($user);
            break;


        case constants::FORM_CREATECOURSE:

            $context = context_system::instance();


            // Set the companyid
            $schoolid = common::get_my_schoolid($context);

            $urlparams = array('schoolid' => $schoolid);

            $companylist = new moodle_url('/my', $urlparams);

            /* next line copied from /course/edit.php */
            $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES,
                    'maxbytes' => $CFG->maxbytes,
                    'trusttext' => false,
                    'noclean' => true);

            $mform = new \block_poodllclassroom\local\form\createcourseform(null, array('schoolid'=>$schoolid,'editoroptions'=> $editoroptions));

            break;

        case constants::FORM_EDITCOURSE:

            $systemcontext = context_system::instance();
            $courseid = $itemid;
            $thecourse=get_course($courseid);


            $coursecontext = context_course::instance($courseid, MUST_EXIST);
            $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES,
                'maxbytes' => $CFG->maxbytes,
                'trusttext' => false,
                'noclean' => true);
            $thecourse = file_prepare_standard_editor($thecourse, 'summary', $editoroptions, $coursecontext, 'course', 'summary',0);
            $mform = new \block_poodllclassroom\local\form\createcourseform(null, array(0,'editoroptions'=> $editoroptions));
            $mform->set_data($thecourse);

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