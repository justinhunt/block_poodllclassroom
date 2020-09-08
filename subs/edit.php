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
 * Klass related management functions
 *
 * @package    block_readseedteacher
 * @copyright  2019 Justin Hunt  {@link http://poodll.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require('../../../config.php');

use block_poodllclassroom\constants;
use block_poodllclassroom\common;

$id        = optional_param('id', 0, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_BOOL);
$type    = optional_param('type', 'sub', PARAM_TEXT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

require_login();

$category = null;
$baseurl = constants::M_URL;



$context = context_system::instance();
require_capability('block/poodllclassroom:manageintegration', $context);

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = new moodle_url($baseurl . '/subs/subs.php', array());
}


$PAGE->set_context($context);
$baseurl = new moodle_url($baseurl . '/subs/edit.php', array('id' => $id, 'type'=>$type));
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$renderer = $PAGE->get_renderer(constants::M_COMP);


switch($type){
    case 'sub':
        $editform = new \block_poodllclassroom\local\form\editsubform();
        break;
    case 'school':
        $editform = new \block_poodllclassroom\local\form\editschoolform();
}


if ($editform->is_cancelled()){
    redirect($returnurl);
}else if($data = $editform->get_data()) {
    switch($type){
        case 'sub':
            if ($data->id) {
                $result=$DB->insert_record(constants::M_TABLE_SUBS,$data);
            } else {
                $updatedata = array('id'=>$data->id,'name'=>$data->name,
                        'maxusers'=>$data->maxusers,
                        'maxcourses'=>$data->maxcourses,
                        'features'=>$data->features,
                        'upstreamkey'=>$data->upstreamkey);
                $result=$DB->update_record(constants::M_TABLE_SUBS,$updatedata);
            }
            break;
        case 'school':
            $updatedata = array('id'=>$data->id,'subid'=>$data->subid);
            $result=$DB->update_record(constants::M_TABLE_SCHOOLS,$updatedata);
    }


    // Redirect to where we were before.
    redirect($returnurl);

}

switch($type){
    case 'sub':
        if ($id) {
            $usedata = $DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$id));
            $editform->set_data($usedata);
        }
        break;
    case 'school':
        $usedata = $DB->get_record(constants::M_TABLE_SCHOOLS,array('id'=>$id));
        $editform->set_data($usedata);
}

$strheading = get_string('subsschoolsplans', constants::M_COMP);
$PAGE->set_title($strheading);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strheading);


echo $renderer->header();
echo $renderer->heading($strheading);
echo $editform->display();
echo $renderer->footer();

