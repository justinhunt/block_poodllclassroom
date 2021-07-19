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
 * Subs manage
 *
 * @package    block_poodllclassroom
 * @copyright  2019 Justin Hunt  {@link http://poodll.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_poodllclassroom\constants;
use block_poodllclassroom\common;
require('../../../config.php');


$id        = required_param('id',  PARAM_TEXT);
$state       = required_param('state',  PARAM_TEXT);

//set the url of the $PAGE
//note we do this before require_login preferably
//so Moodle will send user back here if it bounces them off to login first
$PAGE->set_url(constants::M_URL . '/subs/welcomeback.php',array('id' => $id,'state'=>$state));
$course = get_course(1);
require_login($course);


$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->set_title(get_string('pluginname', constants::M_COMP));
$PAGE->navbar->add(get_string('pluginname', constants::M_COMP));

//company name
//Set the companyid
$companyname = $SITE->fullname;

if($state=='succeeded') {
    $hp = common::retrieve_hosted_page($id);
    if($hp){
        $hpstring = json_encode($hp);
        $passthroughdata = json_decode($hp->hosted_page->pass_thru_content);
        $school = common::get_resold_or_my_school($passthroughdata->schoolid);
        $subscription =$hp->hosted_page->content->subscription;
        if($school) {
            $plan = common::get_plan($passthroughdata->planid);
            common::create_poodllsub($school->id,$school->ownerid, $plan->id, $school->upstreamownerid, $subscription->id,$hpstring );
            $ret = $hpstring;
        }else{
            $ret = "something was not right with that school ...";
        }
    }else{
        $ret = "rubbish hosted page";
    }

}else{
    $ret = "nah no good. state was ...: " . $state;
}



//get our renderer
$renderer = $PAGE->get_renderer(constants::M_COMP);



echo $renderer->header();
echo $renderer->heading($companyname);
echo $ret;
echo $renderer->footer();