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
 * The block_poodllclassroom scheduled task
 *
 * @package    mod_newblock
 * @copyright  Justin Hunt (https://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_poodllclassroom\task;
use block_poodllclassroom\constants;


defined('MOODLE_INTERNAL') || die();

class chargebeesync extends \core\task\scheduled_task {
		
	public function get_name() {
        // Shown in admin screens
        return get_string('chargebeesync_task', constants::M_COMP);
    }
	
	 /**
     *  Run the task
      */
	 public function execute(){
		$trace = new \text_progress_trace();
		$trace->output('running the chargebee sync task now');
         \block_poodllclassroom\common::do_sync_subs($trace);
         \block_poodllclassroom\chargebee_helper::retrieve_process_events($trace);
	}

}

