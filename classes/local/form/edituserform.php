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
require_once($CFG->dirroot.'/user/editlib.php');
require_once($CFG->dirroot . '/blocks/iomad_company_admin/editadvanced_form.php');

class edituserform extends \user_editadvanced_form {
    /**
     * Define the form.
     */
    public function definition() {
        global $USER, $CFG, $COURSE, $PAGE;

        $mform = $this->_form;
        $editoroptions = null;
        $filemanageroptions = null;

        if (!is_array($this->_customdata)) {
            throw new \coding_exception('invalid custom data for user_edit_form');
        }
        $editoroptions = $this->_customdata['editoroptions'];
        $filemanageroptions = $this->_customdata['filemanageroptions'];
        $user = $this->_customdata['user'];
        $userid = $user->id;

        // Accessibility: "Required" is bad legend text.
        $strgeneral  = get_string('general');
        $strrequired = get_string('required');

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', \core_user::get_property_type('id'));
        $mform->addElement('hidden', 'course', $COURSE->id);
        $mform->setType('course', PARAM_INT);

        // Print the required moodle fields first.
        $mform->addElement('header', 'moodle', $strgeneral);

        $auths = \core_component::get_plugin_list('auth');
        $enabled = get_string('pluginenabled', 'core_plugin');
        $disabled = get_string('plugindisabled', 'core_plugin');
        $authoptions = array($enabled => array(), $disabled => array());
        $cannotchangepass = array();
        $cannotchangeusername = array();
        foreach ($auths as $auth => $unused) {
            $authinst = get_auth_plugin($auth);

            if (!$authinst->is_internal()) {
                $cannotchangeusername[] = $auth;
            }

            $passwordurl = $authinst->change_password_url();
            if (!($authinst->can_change_password() && empty($passwordurl))) {
                if ($userid < 1 and $authinst->is_internal()) {
                    // This is unlikely but we can not create account without password
                    // when plugin uses passwords, we need to set it initially at least.
                } else {
                    $cannotchangepass[] = $auth;
                }
            }
            if (is_enabled_auth($auth)) {
                $authoptions[$enabled][$auth] = get_string('pluginname', "auth_{$auth}");
            } else {
                $authoptions[$disabled][$auth] = get_string('pluginname', "auth_{$auth}");
            }
        }

        $mform->addElement('text', 'username', get_string('username'), 'size="20"');
        $mform->addHelpButton('username', 'username', 'auth');
        $mform->setType('username', PARAM_RAW);

        if ($userid !== -1) {
            $mform->disabledIf('username', 'auth', 'in', $cannotchangeusername);
        }
//do not show auth method on edit form
        /*
        $mform->addElement('selectgroups', 'auth', get_string('chooseauthmethod', 'auth'), $authoptions);
        $mform->addHelpButton('auth', 'chooseauthmethod', 'auth');
        */

        $mform->addElement('advcheckbox', 'suspended', get_string('suspended', 'auth'));
        $mform->addHelpButton('suspended', 'suspended', 'auth');

        $mform->addElement('checkbox', 'createpassword', get_string('createpassword', 'auth'));
        $mform->disabledIf('createpassword', 'auth', 'in', $cannotchangepass);

        if (!empty($CFG->passwordpolicy)) {
            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
        }
        $mform->addElement('passwordunmask', 'newpassword', get_string('newpassword'), 'size="20"');
        $mform->addHelpButton('newpassword', 'newpassword');
        $mform->setType('newpassword', \core_user::get_property_type('password'));
        $mform->disabledIf('newpassword', 'createpassword', 'checked');

        $mform->disabledIf('newpassword', 'auth', 'in', $cannotchangepass);

        // Check if the user has active external tokens.
        if ($userid and empty($CFG->passwordchangetokendeletion)) {
            if ($tokens = \webservice::get_active_tokens($userid)) {
                $services = '';
                foreach ($tokens as $token) {
                    $services .= format_string($token->servicename) . ',';
                }
                $services = get_string('userservices', 'webservice', rtrim($services, ','));
                $mform->addElement('advcheckbox', 'signoutofotherservices', get_string('signoutofotherservices'), $services);
                $mform->addHelpButton('signoutofotherservices', 'signoutofotherservices');
                $mform->disabledIf('signoutofotherservices', 'newpassword', 'eq', '');
                $mform->setDefault('signoutofotherservices', 1);
            }
        }

        $mform->addElement('advcheckbox', 'preference_auth_forcepasswordchange', get_string('forcepasswordchange'));
        $mform->addHelpButton('preference_auth_forcepasswordchange', 'forcepasswordchange');
        $mform->disabledIf('preference_auth_forcepasswordchange', 'createpassword', 'checked');

        $mform->addElement('select', 'lang', get_string('preferredlanguage'), get_string_manager()->get_list_of_translations());

        //START OF "SHARED FIELDS"
       //originally was this ...
        // useredit_shared_definition($mform, $editoroptions, $filemanageroptions, $user);

        //but we made trimmed version of that below ... we do not need city/country/email display

        if ($user->id > 0) {
            useredit_load_preferences($user, false);
        }

        $strrequired = get_string('required');
        $stringman = get_string_manager();

        // Add the necessary names.
        foreach (useredit_get_required_name_fields() as $fullname) {
            $purpose = user_edit_map_field_purpose($user->id, $fullname);
            $mform->addElement('text', $fullname,  get_string($fullname),  'maxlength="100" size="30"' . $purpose);
            if ($stringman->string_exists('missing'.$fullname, 'core')) {
                $strmissingfield = get_string('missing'.$fullname, 'core');
            } else {
                $strmissingfield = $strrequired;
            }
            $mform->addRule($fullname, $strmissingfield, 'required', null, 'client');
            $mform->setType($fullname, PARAM_NOTAGS);
        }

        $enabledusernamefields = useredit_get_enabled_name_fields();
        // Add the enabled additional name fields.
        foreach ($enabledusernamefields as $addname) {
            $purpose = user_edit_map_field_purpose($user->id, $addname);
            $mform->addElement('text', $addname,  get_string($addname), 'maxlength="100" size="30"' . $purpose);
            $mform->setType($addname, PARAM_NOTAGS);
        }

        // Do not show email field if change confirmation is pending.
        if ($user->id > 0 and !empty($CFG->emailchangeconfirmation) and !empty($user->preference_newemail)) {
            // IOMAD - Change to allow a manager to cancel the email request change.
            $pageurl = $PAGE->url;
            $pageurl .= "&amp;cancelemailchange=1";
            $notice = get_string('emailchangepending', 'auth', $user);
            $notice .= '<br /><a href="'. $pageurl . '">'
                . get_string('emailchangecancel', 'auth') . '</a>';
            $mform->addElement('static', 'emailpending', get_string('email'), $notice);
        } else {
            $purpose = user_edit_map_field_purpose($user->id, 'email');
            $mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="30"' . $purpose);
            $mform->addRule('email', $strrequired, 'required', null, 'client');
            $mform->setType('email', PARAM_RAW_TRIMMED);
        }
        //the edit advancded form we inherit from will complain if there is no custom picture, so we add one
        //we made the label say "custom data" because I dould not figure out how to add and hide it
        $mform->addElement('static', 'currentpicture', get_string('currentpicture',constants::M_COMP));

        //END OF "SHARED FIELDS"

        // Next the customisable profile fields.
        profile_definition($mform, $userid);

        if ($userid == -1) {
            $btnstring = get_string('createuser');
        } else {
            $btnstring = get_string('updatemyprofile');
        }

        $this->add_action_buttons(false, $btnstring);

        $this->set_data($user);
    }

}