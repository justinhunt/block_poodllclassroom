<?php
/**
 * Helper.
 *
 * @package block_poodllclassroom
 * @author  Justin Hunt - poodll.com
 */

namespace block_poodllclassroom\local\form;

use block_poodllclassroom\constants;

defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir . '/formslib.php');


    /**
     * Helper class.
     *
     * @package block_poodllclassroom
     * @author  Justin Hunt - poodll.com
     */
class uploaduserform extends \moodleform {

    public function __construct($schoolid, $ajaxdata) {
        global $CFG, $USER;

        $this->selectedschool = $schoolid;

        $this->context = \context_coursecat::instance($CFG->defaultrequestcategory);

        $method = 'post';
        $target = '';
        $attributes = null;
        $editable = true;
        \moodleform::__construct(null, array(), $method, $target, $attributes, $editable, $ajaxdata);
    }

    public function definition() {
        $mform = $this->_form;
        $mform->addElement('static', 'instructions','', get_string('uploadinstructions', constants::M_COMP));
        $delimiter_options = array('delim_comma' => get_string('delim_comma', constants::M_COMP),
                 'delim_tab' => get_string('delim_tab', constants::M_COMP),
                 'delim_pipe' => get_string('delim_pipe', constants::M_COMP)
        );
        $mform->addElement('select', 'delimiter', get_string('delimiter', constants::M_COMP), $delimiter_options);
        $mform->setType('delimiter', PARAM_NOTAGS);
        $mform->setDefault('delimiter', 'delim_comma');
        $mform->addRule('delimiter', null, 'required', null, 'client');

        $mform->addElement('textarea', 'importdata', get_string('importdata', constants::M_COMP),array('style'=>'width: 100%'));
        $mform->setType('importdata', PARAM_NOTAGS);
        $mform->addRule('importdata', null, 'required', null, 'client');
        $this->add_action_buttons(false);
    }
}


