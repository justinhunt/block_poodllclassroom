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




    $args = (object) $args;
    $context = $args->context;
    $formname = $args->formname;
    $mform= null;
    $o = '';



    list($ignored, $course) = get_context_info_array($context->id);

    switch($formname){
        case 'createuser':
            $context = context_system::instance();
            iomad::require_capability('block/iomad_company_admin:user_create', $context);

            // Correct the navbar.
            // Set the name for the page.
            $linktext = get_string('createuser', 'block_iomad_company_admin');


            // Set the companyid
            $companyid = iomad::get_my_companyid($context);

            $departmentid=0;
            $licenseid=0;
            $data=null;
            $mform = new \block_poodllclassroom\local\form\createuserform($companyid, $departmentid, $licenseid, $data);

            break;


        case 'createcourse':

            $context = context_system::instance();
            iomad::require_capability('block/iomad_company_admin:createcourse', $context);

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