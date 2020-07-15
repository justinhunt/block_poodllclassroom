<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:32
 */

namespace block_poodllclassroom\settings;

use \block_poodllclassroom\constants;

class sitedetailsform extends baseform
{

    public $type = constants::SETTING_SITEDETAILSFORM;
    public $typestring = constants::SETTING_SITEDETAILSFORM;
    public function custom_definition() {

        $this->add_title(get_string('sitedetailsform_title',constants::M_COMP));
        $this->add_instructions(get_string('sitedetailsform_instructions',constants::M_COMP));
        $this->add_textboxfield('sitefullname',get_string('fullsitename','moodle'));
        $this->add_textboxfield('siteshortname',get_string('shortsitename','moodle'));
        $this->add_textboxfield('supportname',get_string('supportname','admin'));
        $this->add_textboxfield('supportemail',get_string('supportemail','admin'),PARAM_EMAIL);


    }
    public function custom_definition_after_data() {


    }
    public function get_savebutton_text(){
        return get_string('save', constants::M_COMP);
    }

}