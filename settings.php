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
 * Block newblock
 *
 * @package    block_poodllclassroom
 * @copyright  Justin Hunt Neis <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_poodllclassroom\constants;
use block_poodllclassroom\common;

defined('MOODLE_INTERNAL') || die();
if ($ADMIN->fulltree) {


    $settings->add(new admin_setting_configtext(constants::M_COMP . '/chargebeeapikey',
            get_string('chargebeeapikey', constants::M_COMP),
            get_string('chargebeeapikey_desc', constants::M_COMP),
            '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext(constants::M_COMP . '/chargebeesiteprefix',
            get_string('chargebeesiteprefix', constants::M_COMP),
            get_string('chargebeesiteprefix_desc', constants::M_COMP),
            'poodll', PARAM_TEXT));

    $settings->add(new admin_setting_configtext(constants::M_COMP . '/chargebeesiteprefix',
            get_string('chargebeesiteprefix', constants::M_COMP),
            get_string('chargebeesiteprefix_desc', constants::M_COMP),
            'poodll', PARAM_TEXT));

    $settings->add(new admin_setting_configtext(constants::M_COMP . '/resellercoupon',
        get_string('resellercoupon', constants::M_COMP),
        get_string('resellercoupon_desc', constants::M_COMP),
        '-couponcode--', PARAM_TEXT));

    $integrationoptions =common::fetch_integration_options();
    $settings->add(new admin_setting_configselect(constants::M_COMP . '/integration',
            get_string('integrationtype', constants::M_COMP ),
            get_string('integrationtype_desc', constants::M_COMP ),
            constants::M_INTEGRATION_POODLLNET, $integrationoptions));

    $settings->add(new admin_setting_configtext(constants::M_COMP . '/cpapihost',
            get_string('cpapihost', constants::M_COMP),
            get_string('cpapihost_desc', constants::M_COMP),
            '-cpapihost--', PARAM_TEXT));

    $settings->add(new admin_setting_configtext(constants::M_COMP . '/cpapitoken',
            get_string('cpapitoken', constants::M_COMP),
            get_string('cpapitoken_desc', constants::M_COMP),
            '-capitoken--', PARAM_TEXT));

    $settings->add(new admin_setting_configtext(constants::M_COMP . '/ltihost',
            get_string('ltihost', constants::M_COMP),
            get_string('ltihost_desc', constants::M_COMP),
            '-ltihost--', PARAM_TEXT));

    $settings->add(new admin_setting_configtext(constants::M_COMP . '/ltitoken',
            get_string('ltitoken', constants::M_COMP),
            get_string('ltitoken_desc', constants::M_COMP),
            '-ltitoken--', PARAM_TEXT));

    $settings->add(new admin_setting_configcheckbox(constants::M_COMP . '/enablecpapievents',
        get_string('enablecpapievents', constants::M_COMP),
        get_string('enablecpapievents_desc', constants::M_COMP),'1'));
}