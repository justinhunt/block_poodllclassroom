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
use block_poodllclassroom\constants;
use block_poodllclassroom\local\form\enroluserform;

require_once(dirname(__FILE__) . '/../../config.php'); // Creates $PAGE.
require_once($CFG->dirroot .'/blocks/iomad_company_admin/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot.'/local/email/lib.php');


$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$companyid = optional_param('companyid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$departmentid = optional_param('deptid', 0, PARAM_INT);
$groupid = optional_param('groupid', 0, PARAM_INT);

$context = context_system::instance();
require_login();

$params = array('companyid' => $companyid,
                'courseid' => $courseid,
                'deptid' => $departmentid,
                'groupid' => $groupid);

$urlparams = array('companyid' => $companyid);
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}

// Correct the navbar.
// Set the name for the page.
$linktext = get_string('enroluser', constants::M_COMP);
// Set the url.
$linkurl = new moodle_url('/blocks/poodllclassroom/enroluser.php');

// Print the page header.
$PAGE->set_context($context);
$PAGE->set_url($linkurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($linktext);
// Set the page heading.
$PAGE->set_heading(get_string('myhome') . " - $linktext");
if (empty($CFG->defaulthomepage)) {
    $PAGE->navbar->add(get_string('dashboard', 'block_iomad_company_admin'), new moodle_url($CFG->wwwroot . '/my'));
}

// get output renderer
$output = $PAGE->get_renderer('block_iomad_company_admin');
$PAGE->navbar->add($linktext, $linkurl);

require_login(null, false); // Adds to $PAGE, creates $output.
iomad::require_capability('block/iomad_company_admin:company_course_users', $context);
// Set the companyid
$companyid = iomad::get_my_companyid($context);
$parentlevel = company::get_company_parentnode($companyid);
$companydepartment = $parentlevel->id;
$syscontext = context_system::instance();
$company = new company($companyid);

if (iomad::has_capability('block/iomad_company_admin:edit_all_departments', $syscontext)) {
    $userhierarchylevel = $parentlevel->id;
} else {
    $userlevel = $company->get_userlevel($USER);
    $userhierarchylevel = $userlevel->id;
}

$subhierarchieslist = company::get_all_subdepartments($userhierarchylevel);
if (empty($departmentid)) {
    $departmentid = $userhierarchylevel;
}

$userdepartment = $company->get_userlevel($USER);
$departmenttree = company::get_all_subdepartments_raw($userdepartment->id);
$treehtml = $output->department_tree($departmenttree, optional_param('deptid', 0, PARAM_INT));

$departmentselect = new single_select(new moodle_url($linkurl, $urlparams), 'deptid', $subhierarchieslist, $departmentid);
$departmentselect->label = get_string('department', 'block_iomad_company_admin') .
                           $output->help_icon('department', 'block_iomad_company_admin') . '&nbsp';


$usersform = new enroluserform($PAGE->url, $context, $companyid, $departmentid, [$courseid]);
echo $output->header();

// Check the department is valid.
if (!empty($departmentid) && !company::check_valid_department($companyid, $departmentid)) {
    print_error('invaliddepartment', 'block_iomad_company_admin');
}

if ( $usersform->is_cancelled() ||
     optional_param('cancel', false, PARAM_BOOL) ) {
    if ($returnurl) {
        redirect($returnurl);
    } else {
        redirect(new moodle_url('/my'));
    }
} else {

    echo html_writer::start_tag('div', array('class' => 'iomadclear'));
    if ($companyid > 0) {
        $usersform->set_course([$courseid]);
        $usersform->process();
        $usersform = new enroluserform($PAGE->url, $context, $companyid, $departmentid, [$courseid]);
        $usersform->set_course([$courseid]);
        $usersform->set_data(array('groupid' => $groupid));
        echo $usersform->display();
    }
    echo html_writer::end_tag('div');

    echo $output->footer();
}
