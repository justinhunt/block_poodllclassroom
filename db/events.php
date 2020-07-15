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
 * Poodll Net Admin Block
 *
 * @package block_poodllnet
 * @category event
 * @copyright 2020 Justin Hunt  {@link http://poodll.com}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// List of observers.
$observers = array(

    array(
        'eventname'   => '\core\event\user_updated',
            'callback' => '\block_poodllclassroom\event_observer::user_updated',
            'internal' => false
    ),

    array(
        'eventname' => '\core\event\user_deleted',
            'callback' => '\block_poodllclassroom\event_observer::user_deleted',
            'internal' => false
    ),

    array(
        'eventname' => '\core\event\user_created',
            'callback' => '\block_poodllclassroom\event_observer::user_created',
            'internal' => false
    ),

    array(
        'eventname' => '\core\event\course_created',
            'callback' => '\block_poodllclassroom\event_observer::course_created',
            'internal' => false
    ),

    array(
        'eventname' => '\core\event\course_deleted',
            'callback' => '\block_poodllclassroom\event_observer::course_deleted',
            'internal' => false
    ),
);