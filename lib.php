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
            if(!iomad::has_capability('block/iomad_company_admin:user_create', $context)){
                return false;
            }

            // Set the companyid
            $companyid = iomad::get_my_companyid($context);
            $departmentid=0;
            $licenseid=0;
            $courseid=$itemid;
            $courses=[$courseid];
            $actionurl="unused";

            $data=null;
            $mform = new \block_poodllclassroom\local\form\enroluserform($actionurl,$context,$companyid, $departmentid, $courses, $ajaxdata);

            break;

        case constants::FORM_CREATEUSER:
            $context = context_system::instance();
            if(!iomad::has_capability('block/iomad_company_admin:user_create', $context)){
                return false;
            }

            // Set the companyid
            $companyid = iomad::get_my_companyid($context);
            $departmentid=0;
            $licenseid=0;

            $data=null;
            $mform = new \block_poodllclassroom\local\form\createuserform($companyid, $departmentid, $licenseid, $data);

            break;

        case constants::FORM_EDITUSER :
            $context = context_system::instance();
            if(!iomad::has_capability('block/iomad_company_admin:user_create', $context)){
                return false;
            }
            require_once($CFG->dirroot . '/blocks/iomad_company_admin/editadvanced_form.php');
            require_once($CFG->dirroot . '/user/editlib.php');
            require_once($CFG->dirroot . '/user/profile/lib.php');

            // Set the companyid
            $companyid = iomad::get_my_companyid($context);
            $departmentid=0;
            $licenseid=0;
            $userid=$itemid;

            // Check the userid is valid.
            if (!empty($userid) && !company::check_valid_user($companyid, $userid, $departmentid)) {
                print_error('invaliduserdepartment', 'block_iomad_company_management');
                return;
            }

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
                'companyid' => $companyid,
                'user' => $user,
                'filemanageroptions' => $filemanageroptions));
            //$mform = new \block_poodllclassroom\local\form\edituserform(null, array('filemanageroptions'=>$filemanageroptions,'editoroptions'=> $editoroptions, 'user'=>$user));

            $mform->set_data($user);
            break;

        case constants::FORM_EDITUSER . 'x':
            $context = context_system::instance();
            if(!iomad::has_capability('block/iomad_company_admin:user_create', $context)){
                return false;
            }

            // Set the companyid
            $companyid = iomad::get_my_companyid($context);
            $departmentid=0;
            $licenseid=0;
            $userid=$itemid;

            // Check the userid is valid.
            if (!empty($userid) && !company::check_valid_user($companyid, $userid, $departmentid)) {
                print_error('invaliduserdepartment', 'block_iomad_company_management');
                return;
            }

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
            $mform = new \block_poodllclassroom\local\form\edituserform(null, array('filemanageroptions'=>$filemanageroptions,'editoroptions'=> $editoroptions, 'user'=>$user));

            $mform->set_data($user);

            break;


        case constants::FORM_CREATECOURSE:

            $context = context_system::instance();
           if(! iomad::has_capability('block/iomad_company_admin:createcourse', $context)){
               return false;
           }

            // Correct the navbar.
            // Set the name for the page.
            $linktext = get_string('createcourse_title', 'block_iomad_company_admin');

            // Set the url.
            $linkurl = new moodle_url('/blocks/iomad_company_admin/company_course_create_form.php');

            // Set the companyid
            $companyid = iomad::get_my_companyid($context);

            $urlparams = array('companyid' => $companyid);

            $companylist = new moodle_url('/my', $urlparams);

            /* next line copied from /course/edit.php */
            $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES,
                    'maxbytes' => $CFG->maxbytes,
                    'trusttext' => false,
                    'noclean' => true);

            $mform = new \block_poodllclassroom\local\form\createcourseform(null, array('companyid'=>$companyid,'editoroptions'=> $editoroptions));

            break;

        case constants::FORM_EDITCOURSE:

            $systemcontext = context_system::instance();
           if(!iomad::has_capability('block/iomad_company_admin:createcourse', $context)){
               return false;
           }

            // Set the companyid
            $companyid = iomad::get_my_companyid($systemcontext);
            $courseid = $itemid;
            // Check the courseid is valid.
            if (!empty($courseid) && !empty($companyid)) {
                $thecourse= common::fetch_company_course($companyid,$courseid);
                if(!$thecourse) {
                    print_error('invalidcourse', 'block_poodllclassroom');
                    return false;
                }
            }

            $coursecontext = context_course::instance($courseid, MUST_EXIST);
            $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES,
                'maxbytes' => $CFG->maxbytes,
                'trusttext' => false,
                'noclean' => true);
            $thecourse = file_prepare_standard_editor($thecourse, 'summary', $editoroptions, $coursecontext, 'course', 'summary',0);
            $mform = new \block_poodllclassroom\local\form\createcourseform(null, array('companyid'=>$companyid,'editoroptions'=> $editoroptions));

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