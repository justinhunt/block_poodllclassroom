<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/03/13
 * Time: 19:32
 */

namespace block_poodllclassroom\settings;

use \block_poodllclassroom\constants;

class webhooksform extends baseform
{

    public $type = constants::SETTING_WEBHOOKSFORM;
    public $typestring = constants::SETTING_WEBHOOKSFORM;
    public function custom_definition() {

        $this->add_title(get_string('webhook_title',constants::M_COMP));
        $this->add_instructions(get_string('webhook_instructions',constants::M_COMP));
        $this->add_webhooks();


    }
    public function custom_definition_after_data() {


    }
    public function get_savebutton_text(){
        return get_string('save', constants::M_COMP);
    }

}