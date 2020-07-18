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

use \company;
use \iomad;

require_once($CFG->dirroot.'/lib/formslib.php');

class createuserform extends \block_iomad_company_admin\forms\user_edit_form {
    protected $title = '';
    protected $description = '';
    protected $selectedcompany = 0;
    protected $context = null;

    public function __construct($companyid, $departmentid, $licenseid=0, $ajaxdata) {
        global $CFG, $USER;

        $this->selectedcompany = $companyid;
        $this->departmentid = $departmentid;
        $this->licenseid = $licenseid;
        $company = new company($this->selectedcompany);
        $this->companyname = $company->get_name();
        $parentlevel = company::get_company_parentnode($company->id);
        $this->companydepartment = $parentlevel->id;
        $systemcontext = \context_system::instance();

        if (\iomad::has_capability('block/iomad_company_admin:edit_all_departments', $systemcontext)) {
            $userhierarchylevel = $parentlevel->id;
        } else {
            $userlevel = $company->get_userlevel($USER);
            $userhierarchylevel = $userlevel->id;
        }

        $this->subhierarchieslist = company::get_all_subdepartments($userhierarchylevel);
        if ($this->departmentid == 0) {
            $departmentid = $userhierarchylevel;
        } else {
            $departmentid = $this->departmentid;
        }
        $this->userdepartment = $userhierarchylevel;

        $options = array('context' => $this->context,
                'multiselect' => true,
                'companyid' => $this->selectedcompany,
                'departmentid' => $departmentid,
                'subdepartments' => $this->subhierarchieslist,
                'parentdepartmentid' => $parentlevel,
                'showopenshared' => true,
                'license' => false);

        $this->currentcourses = new \potential_subdepartment_course_selector('currentcourses', $options);
        $this->currentcourses->set_rows(20);
        $this->context = \context_coursecat::instance($CFG->defaultrequestcategory);

        $method='post';
        $target='';
        $attributes=null;
        $editable=true;
        parent::__construct(null, array(), $method,$target,$attributes,$editable,$ajaxdata);
    }




}