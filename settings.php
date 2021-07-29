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

}