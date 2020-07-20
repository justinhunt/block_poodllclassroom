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

    function fetch_block_content($context, $company,$users){

        //userlist
        //if we have items, show em. Data tables will make it pretty
        //Prepare datatable(before header printed)
        $tableid = '' . constants::M_CLASS_USERLIST . '_' . '_opts_9999';
        $this->setup_datatables($tableid);

        $contextid = $context->id;
        $title = get_string('createcourse',constants::M_COMP);
        $content = "a whole lot of content going on";
        $containertag = 'createcourse';
        $amodalcontainer = $this->fetch_modalcontainer($title,$content,$containertag);

        $createuserbutton = $this->js_trigger_button('createuser', true,
                get_string('createuser',constants::M_COMP), 'btn-primary');

        $createcoursebutton = $this->js_trigger_button('createcourse', true,
                get_string('createcourse',constants::M_COMP), 'btn-primary');

        //we attach an event to it. The event comes from a JS AMD module also in this plugin
        $opts=array('modulecssclass' => 'block_poodllclassroom', 'contextid'=>$contextid,'tableid'=>$tableid);
        $this->page->requires->js_call_amd(constants::M_COMP . "/blockcontroller", 'init', array($opts));

         $content = $createcoursebutton . '<br>';

         $content .= $createuserbutton;



        $visible=false;
        if($users) {
            $visible = true;
        }else{
            $users=[];
        }

        $content .= $this->create_user_list($users,$tableid,$visible );
        $content .= $this->no_list_users(!$visible);

        //this inits the js for the list helper page
    //    $opts=array('modulecssclass'=>constants::M_CLASS, 'cmid'=>$cm->id, 'moduleid'=>$moduleinstance->id,'authmode'=>'normal', 'max'=>$max);
    //    $this->page->requires->js_call_amd("mod_cpassignment/listhelper", 'init', array($opts));


         $content .= $amodalcontainer;
         return $content;

    }



    //In this function we prepare and display the content that goes in the block
    function fetch_block_content_old($courseid){
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

        //we attach an event to it. The event comes from a JS AMD module also in this plugin
        $opts=array('modulecssclass' => 'block_poodllclassroom');
        $this->page->requires->js_call_amd(constants::M_COMP . "/triggeralert", 'init', array($opts));

        return $content;
    }

    function create_user_list($users,$tableid,$visible){


        $data = [];
        $data['display'] = $visible ? 'block' : 'none';
        $data['tableid']=$tableid;
        $data['items']=[];
        //loop through the items,massage data and add to table
        //itemname itemid,filename,itemdate, id
        $currentitem=0;
        foreach ($users as $user) {
            $ditem=[];
            $ditem['id']= $user->id;
            $ditem['firstname'] = $user->firstname;
            $ditem['lastname'] =  $user->lastname;
            $ditem['date'] = date("Y-m-d H:i:s",$user->timecreated);
            $data['items'][]=$ditem;

        }
        return $this->render_from_template('block_poodllclassroom/userlisttable', $data);

    }

    /**
     * No items, thats too bad
     */
    public function no_list_users($visible){
        $data=[];
        $data['display'] = $visible ? 'block' : 'none';
        return $this->render_from_template('block_poodllclassroom/nouserscontainer', $data);
    }

    function setup_datatables($tableid){
        global $USER;

        $tableprops = array();
        $columns = array();
        //for cols .. .'itemname', 'itemtype', 'itemtags','timemodified', 'edit','delete'
        $columns[0]=null;
        $columns[1]=null;
        $columns[2]=null;
        $columns[3]=array('orderable'=>false);
        $columns[4]=array('orderable'=>false);
        $tableprops['columns']=$columns;

        //default ordering
        $order = array();
        $order[0] =array(2, "desc");
        $tableprops['order']=$order;

        //here we set up any info we need to pass into javascript
        $opts =Array();
        $opts['tableid']=$tableid;
        $opts['tableprops']=$tableprops;
        $this->page->requires->js_call_amd(constants::M_COMP . "/datatables", 'init', array($opts));
        $this->page->requires->css( new \moodle_url('https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'));
    }

    /**
     *  Show a single button.
     */
    public function js_trigger_button($buttontag, $visible, $buttonlabel, $bootstrapclass='btn-primary'){

        $buttonclass =constants::M_CLASS  . '_' . $buttontag . '_btn';
        $containerclass = $buttonclass . '_cnt';
        $button = \html_writer::link('#', $buttonlabel, array('class'=>'btn ' . $bootstrapclass . ' ' . $buttonclass,'type'=>'button','id'=>$buttonclass));
        $visibleclass = '';
        if(!$visible){$visibleclass = 'hide';}
        $ret = \html_writer::div($button, $containerclass . ' ' .  $visibleclass);
        return $ret;
    }

    //fetch modal container
    function fetch_modalcontainer($title,$content,$containertag){
        $data=[];
        $data['title']=$title;
        $data['content']=$content;
        $data['containertag']=$containertag;
        return $this->render_from_template('block_poodllclassroom/modalcontainer', $data);
    }


    //fetch modal content
    function fetch_modalcontent($title,$content){
        $data=[];
        $data['title']=$title;
        $data['content']=$content;
        return $this->render_from_template('block_poodllclassroom/modalcontent', $data);
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