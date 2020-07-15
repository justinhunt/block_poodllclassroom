<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:32
 */

namespace block_poodllclassroom\settings;

use \block_poodllclassroom\constants;

class facebookauthform extends baseform
{

    public $type = constants::SETTING_FACEBOOKAUTH;
    public $typestring = constants::SETTING_FACEBOOKAUTH;
    public function custom_definition() {

        $this->add_title(get_string('facebookauth_title',constants::M_COMP));
        $this->add_instructions(get_string('facebookauth_instructions',constants::M_COMP));
        $this->add_clientid();
        $this->add_clientsecret();

    }
    public function custom_definition_after_data() {


    }
    public function get_savebutton_text(){
        return get_string('save', constants::M_COMP);
    }

}