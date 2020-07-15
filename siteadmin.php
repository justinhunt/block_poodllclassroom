<?php

///////////////////////////////////////////////////////////////////////////
//                                                                       //
// This file is part of Moodle - http://moodle.org/                      //
// Moodle - Modular Object-Oriented Dynamic Learning Environment         //
//                                                                       //
// Moodle is free software: you can redistribute it and/or modify        //
// it under the terms of the GNU General Public License as published by  //
// the Free Software Foundation, either version 3 of the License, or     //
// (at your option) any later version.                                   //
//                                                                       //
// Moodle is distributed in the hope that it will be useful,             //
// but WITHOUT ANY WARRANTY; without even the implied warranty of        //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         //
// GNU General Public License for more details.                          //
//                                                                       //
// You should have received a copy of the GNU General Public License     //
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.       //
//                                                                       //
///////////////////////////////////////////////////////////////////////////

/**
 * Block new_block view.php
 * @package   block_poodllclassroom
 * @copyright 2018 Justin Hunt (https://poodll.com)
 * @author    Justin Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_poodllclassroom\constants;
use block_poodllclassroom\common;

require('../../config.php');

$courseid = optional_param('courseid', 1,PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
//$showsetting = optional_param('showsetting', constants::SETTING_NONE, PARAM_TEXT);
$type = optional_param('type', constants::SETTING_NONE, PARAM_TEXT);

require_login();

//we need this to write and get admin settings
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/course/format/lib.php');

$context = context_course::instance($courseid, MUST_EXIST);
$course = get_course($courseid);
require_capability('block/poodllclassroom:managesite', $context);

$PAGE->set_course($course);
$PAGE->set_context($context);
$PAGE->set_url(constants::M_URL . '/siteadmin.php', array('courseid'=>$courseid, 'type'=>$type));
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('course');
$PAGE->navbar->add(get_string('siteadmin', constants::M_COMP));
$renderer = $PAGE->get_renderer(constants::M_COMP);

$returnonfailurl = new moodle_url(constants::M_URL . '/siteadmin.php', array('courseid'=>$courseid,'type'=>$type));
if(empty($returnurl)){
    $returnurl = new moodle_url($CFG->wwwroot . '/my');
}


//get the mform for our attempt
$mform=false;
switch($type){

    case constants::SETTING_MICROSOFTAUTH:
        $mform = new \block_poodllclassroom\settings\microsoftauthform(null,
                array());
        break;

    case constants::SETTING_FACEBOOKAUTH:
        $mform = new \block_poodllclassroom\settings\facebookauthform(null,
                array());
        break;

    case constants::SETTING_GOOGLEAUTH:
        $mform = new \block_poodllclassroom\settings\googleauthform(null,
                array());
        break;

    case constants::SETTING_WEBHOOKSFORM:
        $mform = new \block_poodllclassroom\settings\webhooksform(null,
                array());
        break;

    case constants::SETTING_ENROLKEYFORM:
        $mform = new \block_poodllclassroom\settings\enrolkeyform(null,
                array());
        break;
    case constants::SETTING_SITEDETAILSFORM:
        $mform = new \block_poodllclassroom\settings\sitedetailsform(null,
                array());
        break;


    case constants::SETTING_NONE:
    default:

}

//if the cancel button was pressed, we are out of here
if ($mform && $mform->is_cancelled()) {
    redirect($returnurl);
    exit;
}

//if we have data, then our job here is to save it and return to the quiz edit page
if ($mform && $data = $mform->get_data()) {
    require_sesskey();
    $success=false;
    //type specific settings
    switch($type) {
        case constants::SETTING_MICROSOFTAUTH:
        case constants::SETTING_GOOGLEAUTH:
        case constants::SETTING_FACEBOOKAUTH:
            if($type==constants::SETTING_MICROSOFTAUTH){$usename='Microsoft';}
            elseif($type==constants::SETTING_GOOGLEAUTH){$usename='Google';}
            elseif($type==constants::SETTING_FACEBOOKAUTH){$usename='Facebook';}
            $rec = $DB->get_record('oauth2_issuer',array('name'=>$usename),'id');
            if($rec){
                $issuer = new stdClass();
                $issuer->id=$rec->id;
                $issuer->clientid=$data->clientid;
                $issuer->clientsecret=$data->clientsecret;
                $success =$DB->update_record('oauth2_issuer',$issuer);
            }

            break;
        case constants::SETTING_WEBHOOKSFORM:
            //remove all the current items
            $allitems=\local_trigger\webhook\webhooks::fetch_items();
            foreach($allitems as $item){
                \local_trigger\webhook\webhooks::delete_item($item->id);
            }

            //Add each new one
            $hookcount = constants::M_HOOKCOUNT;
            $webhooksinserted=0;
            for($hooknumber=0;$hooknumber<$hookcount;$hooknumber++) {
                $event = $data->{'event' . $hooknumber};
                $webhook = $data->{'webhook' . $hooknumber};
                $description = $data->{'description' . $hooknumber};
                $enabled = $data->{'enabled' . $hooknumber};

                if(!empty($webhook && !empty($event))) {
                    $theitem = new stdClass;
                    $theitem->id = null;
                    $theitem->authid = $USER->id;  //do this better soon
                    $theitem->webhook = $webhook;
                    $theitem->event = $event;
                    $theitem->description = $description;
                    $theitem->enabled = $enabled;
                    $theitem->modifiedby = $USER->id;
                    $theitem->timemodified = time();

                    $theitem->id = \local_trigger\webhook\webhooks::add_item($theitem);

                    if ($theitem->id) {
                        $webhooksinserted++;
                    }
                }
            }//end of loop
            $success=true;
            break;
        case constants::SETTING_ENROLKEYFORM:
            $sql= 'SELECT enrol.*, role.shortname as rolename FROM {enrol} enrol INNER JOIN {role} role ON enrol.roleid = role.id';
            $sql .= ' WHERE status=0 AND enrol="self"';
            $enrolmethods = $DB->get_records_sql($sql,array());
            foreach ($enrolmethods as $method){
                if($method->courseid > 1){
                    if(isset($data->{'enrolkey' . '_' . $method->id})) {
                        if($data->{'enrolkey' . '_' . $method->id} != $method->password){
                            $item = new stdClass();
                            $item->id= $method->id;
                            $item->password = trim($data->{'enrolkey' . '_' . $method->id});
                            $DB->update_record('enrol',$item);
                        }
                    }
                }
            }
            $success=true;
            break;
        case constants::SETTING_SITEDETAILSFORM:
            $fullname = new \admin_setting_sitesettext('fullname', new lang_string('fullsitename'), '', NULL);
            $shortname = new \admin_setting_sitesettext('shortname', new lang_string('fullsitename'), '', NULL);
            $fullname->write_setting($data->sitefullname);
            $shortname->write_setting($data->siteshortname);
            set_config('supportname',$data->supportname);
            set_config('supportemail',$data->supportemail);
            $success=true;
            break;
        case constants::SETTING_MANAGEUSERS:
            //managing users happen on the regular moodle page, we do not actually come here to save the details
            //for consistency it listed here.
            break;
        case constants::SETTING_NONE:
        default:
    }
    //redirect back here on fail
    if(!$success){
        redirect($returnonfailurl, get_string('failedsetting',constants::M_COMP,
                common::fetch_settings_title($type)));
    }else {
        //go back to top page on success
        redirect($returnurl, get_string('updatedsetting', constants::M_COMP,
                common::fetch_settings_title($type)));
    }



}

//if  we got here, there was no cancel, and no form data, so we are showing the form
/*
$formtypes =[constants::SETTING_MICROSOFTAUTH,
        constants::SETTING_FACEBOOKAUTH,
        constants::SETTING_GOOGLEAUTH,
        constants::SETTING_WEBHOOKSFORM,
        constants::SETTING_ENROLKEYFORM,
        constants::SETTING_MANAGEUSERS];
*/
$formtypes=[$type];
$mforms=[];
foreach($formtypes as $type) {
    $data=new stdClass;
    //$data->type=$type;
    switch ($type) {
        case constants::SETTING_MICROSOFTAUTH:
            $mform = new \block_poodllclassroom\settings\microsoftauthform(null,
                    array());
            $data = $DB->get_record('oauth2_issuer',array('name'=>'Microsoft'),'clientid,clientsecret');
            if($data && $data->clientid!='empty' && $data->clientsecret!='empty') {
                $mform->set_data($data);
            }
            $mforms[]=$mform;
            break;

        case constants::SETTING_FACEBOOKAUTH:
            $mform = new \block_poodllclassroom\settings\facebookauthform(null,
                    array());
            $data = $DB->get_record('oauth2_issuer',array('name'=>'Facebook'),'clientid,clientsecret');
            if($data && $data->clientid!='empty' && $data->clientsecret!='empty') {
                $mform->set_data($data);
            }
            $mforms[]=$mform;
            break;
        case constants::SETTING_GOOGLEAUTH:
            $mform = new \block_poodllclassroom\settings\googleauthform(null,
                    array());
            $data = $DB->get_record('oauth2_issuer',array('name'=>'Google'),'clientid,clientsecret');
            if($data && $data->clientid!='empty' && $data->clientsecret!='empty') {
                $mform->set_data($data);
            }
            $mforms[]=$mform;
            break;

        case constants::SETTING_WEBHOOKSFORM:
            $mform = new \block_poodllclassroom\settings\webhooksform(null,
                    array());
            //get all the current items
            $allitems=\local_trigger\webhook\webhooks::fetch_items();
            $hookcount = constants::M_HOOKCOUNT;
            for($hooknumber=0;$hooknumber<$hookcount;$hooknumber++) {
                $item=array_shift($allitems);
                if($item) {
                    $data->{'event' . $hooknumber} = $item->event;
                    $data->{'webhook' . $hooknumber} = $item->webhook;
                    $data->{'description' . $hooknumber} = $item->description;
                    $data->{'enabled' . $hooknumber} = $item->enabled;
                }
            }
            $mform->set_data($data);
            $mforms[]=$mform;
            break;
        case constants::SETTING_ENROLKEYFORM:
            $mform = new \block_poodllclassroom\settings\enrolkeyform(null,
                    array());
            $sql= 'SELECT enrol.*, role.shortname as rolename FROM {enrol} enrol INNER JOIN {role} role ON enrol.roleid = role.id';
            $sql .= ' WHERE status=0 AND enrol="self"';
            $enrolmethods = $DB->get_records_sql($sql,array());
            foreach ($enrolmethods as $method){
                if($method->courseid > 1){
                    if(!empty($method->password)) {
                        $data->{'enrolkey' . '_' . $method->id} = $method->password;
                    }else{
                        $data->{'enrolkey' . '_' . $method->id} = '';
                    }
                }
            }
            $mform->set_data($data);
            $mforms[]=$mform;
            break;

        case constants::SETTING_SITEDETAILSFORM:
            $mform = new \block_poodllclassroom\settings\sitedetailsform(null,
                    array());
            $fullname = new \admin_setting_sitesettext('fullname', new lang_string('fullsitename'), '', NULL);
            $shortname = new \admin_setting_sitesettext('shortname', new lang_string('fullsitename'), '', NULL);
            $data->sitefullname = $fullname->get_setting();
            $data->siteshortname = $shortname->get_setting();;
            $data->supportname = get_config('core','supportname');
            $data->supportemail = get_config('core','supportemail');
            $mform->set_data($data);
            $mforms[]=$mform;
            break;

        case constants::SETTING_NONE:
        default:
    }
}

$PAGE->navbar->add(get_string('edit'), $returnurl);
$PAGE->navbar->add(get_string('editingsettings', constants::M_COMP));


echo $renderer->header();
foreach($mforms as $mform) {
    $mform->display();
}
echo $renderer->footer();