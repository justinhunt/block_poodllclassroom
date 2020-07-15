<?php

namespace block_poodllclassroom\settings;

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
 * Forms for pchat Activity
 *
 * @package    block_poodllclassroom
 * @author     Justin Hunt <poodllsupport@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Justin Hunt  http://poodll.com
 */

//why do we need to include this?
require_once($CFG->libdir . '/formslib.php');

use \block_poodllclassroom\constants;
use \block_poodllclassroom\common;

/**
 * Abstract class that item type's inherit from.
 *
 * This is the abstract class that add item type forms must extend.
 *
 * @abstract
 * @copyright  2019 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class baseform extends \moodleform {

    /**
     * This is used to identify this itemtype.
     * @var string
     */
    public $type;

    /**
     * The simple string that describes the item type e.g. audioitem, textitem
     * @var string
     */
    public $typestring;



    /**
     * True if this is a standard item of false if it does something special.
     * items are standard items
     * @var bool
     */
    protected $standard = true;

    /**
     * Each item type can and should override this to add any custom elements to
     * the basic form that they want
     */
    public function custom_definition() {}

    /**
     * Item types can override this to add any custom elements to
     * the basic form that they want
     */
    public function custom_definition_after_data() {}

    /**
     * Used to determine if this is a standard item or a special item
     * @return bool
     */
    public final function is_standard() {
        return (bool)$this->standard;
    }

    /**
     * Add the required basic elements to the form.
     *
     * This method adds the basic elements to the form including title and contents
     * and then calls custom_definition();
     */
    public final function definition() {
        $mform = $this->_form;


        if ($this->standard === true) {
            $mform->addElement('hidden', 'type',$this->type);
            $mform->setType('type', PARAM_TEXT);

        }

        $this->custom_definition();

        $savebutton_text = $this->get_savebutton_text();

        //add the action buttons
        $this->add_action_buttons(get_string('cancel'), $savebutton_text);

    }

    public final function definition_after_data() {
        parent::definition_after_data();
        $this->custom_definition_after_data();
    }
    public function get_savebutton_text(){
        return get_string('saveitem', constants::M_COMP);
    }

    protected function add_textboxfield($fieldname,$fieldlabel, $fieldtype=PARAM_TEXT) {
        $this->_form->addElement('text', $fieldname, $fieldlabel);
        $this->_form->setDefault($fieldname, '');
        $this->_form->setType($fieldname, $fieldtype);
    }

    protected function add_clientid() {
        $fieldname = 'clientid';
        $this->_form->addElement('text', $fieldname, get_string($fieldname, constants::M_COMP));
        $this->_form->setDefault($fieldname, '');
        $this->_form->setType($fieldname, PARAM_TEXT);
    }
    protected function add_clientsecret() {
        $fieldname = 'clientsecret';
        $this->_form->addElement('text', $fieldname, get_string($fieldname, constants::M_COMP));
        $this->_form->setDefault($fieldname, '');
        $this->_form->setType($fieldname, PARAM_TEXT);
    }

    protected function add_webhooks() {
        $hookcount = constants::M_HOOKCOUNT;
        $eventarray = \report_eventlist_list_generator::get_all_events_list(false);
        foreach ($eventarray as $key=>$value){
            $eventarray[$key]=$key;
        }
        for($hooknumber=0;$hooknumber<$hookcount;$hooknumber++) {
            $fieldname = 'Webhook_' . $hooknumber;
            $fieldlabel = 'Webhook #' . ($hooknumber + 1);
            $this->_form->addElement('static',$fieldname,'', $fieldlabel);

            $fieldname = 'event' . $hooknumber;
            $this->_form->addElement('select', $fieldname, get_string('event', 'local_trigger'), $eventarray);
            //$this->_form->addRule($fieldname, get_string('required'), 'required', null, 'client');

            $fieldname = 'webhook' . $hooknumber;
            $this->_form->addElement('text', $fieldname, get_string('webhook', 'local_trigger'), array('size' => 70));
            $this->_form->setType($fieldname, PARAM_TEXT);
           // $this->_form->addRule($fieldname, get_string('required'), 'required', null, 'client');

            $fieldname = 'description' . $hooknumber;
            $this->_form->addElement('text', $fieldname, get_string('description', 'local_trigger'), array('size' => 70));
            $this->_form->setType($fieldname, PARAM_TEXT);
            $this->_form->setDefault($fieldname, '');

            $fieldname = 'enabled' . $hooknumber;
            $this->_form->addElement('selectyesno', $fieldname, get_string('enabled', 'local_trigger'));
        }

    }

    protected function add_enrolmentkeys() {
        global $DB;
        $courses=get_courses();

        foreach ($courses as $course){
            $sql= 'SELECT enrol.*, role.shortname as rolename FROM {enrol} enrol INNER JOIN {role} role ON enrol.roleid = role.id';
            $sql .= ' WHERE status=0 AND enrol="self" AND courseid=:courseid';
            $enrolmethods = $DB->get_records_sql($sql,array('courseid'=>$course->id));

            if($enrolmethods) {
                $fieldname = 'CourseName';
                $fieldlabel = $course->fullname;
                $this->_form->addElement('static', $fieldname, '', $fieldlabel);
                foreach($enrolmethods as $method) {

                    $fieldname = 'enrolkey_' . $method->id;
                    $fieldlabel = !empty($method->name) ? $method->name : get_string('pluginname', 'enrol_self');
                    $fieldlabel .="(" . $method->rolename.  ")";
                    $this->_form->addElement('text', $fieldname, $fieldlabel, array('size' => 70));
                    $this->_form->setType($fieldname, PARAM_TEXT);
                    $this->_form->setDefault($fieldname, '');
                }
            }

        }

    }

    protected final function add_title($title) {
        $titlediv = \html_writer::div($title,'block_poodllclassroom_formtitle');
        $this->_form->addElement('static','title','', $titlediv);
    }

    protected final function add_instructions($instructions) {
        $instructionsdiv = \html_writer::div($instructions,'block_poodllclassroom_forminstructions');
        $this->_form->addElement('static','title','', $instructionsdiv);
    }



    protected final function add_sampletempltateamd_field($name, $label) {
        global $CFG, $PAGE, $OUTPUT;
        require_once("$CFG->libdir/outputcomponents.php");

        $checks = array();
        $this->_form->addElement('hidden', $name);
        $this->_form->setType($name,PARAM_TEXT);

        foreach ($this->users as $user){
            $user_picture=new \user_picture($user);
            $picurl = $user_picture->get_url($PAGE);

            $onecheck=array();
            $onecheck['name']=$name;
            $onecheck['userpic']=$picurl;
            $onecheck['username']=fullname($user);
            $onecheck['value']=$user->id;
            $checks[] = $onecheck;
        }
        $staticcontent = $OUTPUT->render_from_template(constants::M_COMP . '/usercombo', array('checks'=>$checks));
        $this->_form->addElement('static', 'combo_' . $name, $label, $staticcontent);


        $opts =Array();
        $opts['container']='usertogglegroup';
        $opts['item']='usertoggleitem';
        $opts['updatecontrol']=$name;
        $opts['mode']='checkbox';
        $opts['maxchecks']=4;
        $PAGE->requires->js_call_amd("mod_pchat/toggleselected", 'init', array($opts));
    }

    /**
     * A function that gets called upon init of this object by the calling script.
     *
     * This can be used to process an immediate action if required. Currently it
     * is only used in special cases by non-standard item types.
     *
     * @return bool
     */
    public function construction_override($itemid,  $pchat) {
        return true;
    }
}