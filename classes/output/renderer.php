<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace block_poodllclassroom\output;

use \block_poodllclassroom\constants;
use \block_poodllclassroom\common;


class renderer extends \plugin_renderer_base {


    /**
     * Return HTML to display limited header
     */
    public function header(){
        return $this->output->header();
    }

    //In this function we prepare and display the content that goes in the block
    function fetch_block_content($courseid){
        global $USER;


        //show our intro text
        $content = '';
        $content .= '<br />' . get_string('welcomeuser', constants::M_COMP,$USER) . '<br />';

        $items = [];

        //oauth 2 menu
        if(get_config(constants::M_COMP,'allowoauth2')){
            $items[]=constants::SETTING_FACEBOOKAUTH;
            $items[]=constants::SETTING_GOOGLEAUTH;
            $items[]=constants::SETTING_MICROSOFTAUTH;
        }
        //webhooks
        if(get_config(constants::M_COMP,'allowwebhooks')){
            $items[]= constants::SETTING_WEBHOOKSFORM;
        }

        //enrol keys and manage users
        $items[]= constants::SETTING_SITEDETAILSFORM;
        $items[]= constants::SETTING_ENROLKEYFORM;
        $items[]= constants::SETTING_MANAGEUSERS;
        $items[]= constants::SETTING_MANAGECOURSES;
        $items[]= constants::SETTING_ADDCOURSE;

        $settings = [];
        foreach ($items as $item){
            $link =common::fetch_settings_url($item,$courseid);
            $displayname =common::fetch_settings_title($item);
            $setting=['url'=>$link->out(false),'displayname'=>$displayname];
            $settings[]=$setting;
        }

        $data=['settings'=>$settings];
        $content .= $this->render_from_template('block_poodllclassroom/tilescontainer', $data);

        //show our link to the view page

        //$content .= \html_writer::link($link, get_string('gotoadminpage', constants::M_COMP));
        return $content;
    }

    //In this function we prepare and display the content for the page
    function display_view_page($blockid, $courseid){
        global $USER;

        $content = '';
        $content .= '<br />' . get_string('welcomeuser', constants::M_COMP,$USER) . '<br />';
        $content .= $this->fetch_dosomething_button($blockid,$courseid);
        $content .= $this->fetch_triggeralert_button();

        //a page must have a header
        echo $this->output->header();
        //and of course our page content
        echo $content;
        //a page must have a footer
        echo $this->output->footer();
    }

    function fetch_dosomething_button($blockid, $courseid){
        //single button is a Moodle helper class that creates simple form with a single button for you
        $triggerbutton = new \single_button(
            new \moodle_url(constants::M_URL . '/view.php',array('blockid'=>$blockid,'courseid'=>$courseid,'dosomething'=>1)),
            get_string('dosomething', constants::M_COMP), 'get');

        return \html_writer::div( $this->render($triggerbutton),constants::M_COMP . '_triggerbutton');
    }
    function fetch_triggeralert_button(){
        //these are attributes for a simple html button.
        $attributes = array();
        $attributes['type']='button';
        $attributes['id']= \html_writer::random_id(constants::M_COMP . '_');
        $attributes['class']=constants::M_COMP . '_triggerbutton';
        $button = \html_writer::tag('button',get_string('triggeralert', constants::M_COMP),$attributes);

        //we attach an event to it. The event comes from a JS AMD module also in this plugin
        $opts=array('buttonid' => $attributes['id']);
        $this->page->requires->js_call_amd(constants::M_COMP . "/triggeralert", 'init', array($opts));

        //we want to make our language strings available to our JS button too
        //strings for JS
        $this->page->requires->strings_for_js(array(
            'triggeralert_message'
        ),
            constants::M_COMP);

        //finally return our button for display
        return $button;
    }
}