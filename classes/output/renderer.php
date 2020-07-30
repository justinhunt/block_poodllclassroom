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

    function fetch_block_content($context, $company,$users, $courses){

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
        if($courses) {
            $visible = true;
        }else{
            $courses=[];
        }
        $content .= $this->create_course_list($courses,$visible );
        $content .= $this->no_courses(!$visible);



        $visible=false;
        if($users) {
            $visible = true;
        }else{
            $users=[];
        }

        $content .= $this->create_user_list($users,$tableid,$visible );
        $content .= $this->no_users(!$visible);

         $content .= $amodalcontainer;
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
            if($user->lastaccess) {
                $lastaccess = date("Y-m-d H:i:s", $user->lastaccess);
            }else{
                $lastaccess = '--:--';
            }
            $ditem=[];
            $ditem['id']= $user->id;
            $ditem['firstname'] = $user->firstname;
            $ditem['lastname'] =  $user->lastname;
            $ditem['lastaccess'] = $lastaccess;
            $data['items'][]=$ditem;

        }
        return $this->render_from_template('block_poodllclassroom/userlisttable', $data);

    }

    /**
     * No items, thats too bad
     */
    public function no_users($visible){
        $data=[];
        $data['display'] = $visible ? 'block' : 'none';
        return $this->render_from_template('block_poodllclassroom/nouserscontainer', $data);
    }

    function create_course_list($courses,$visible){
        global $CFG;

        $data = [];
        $data['display'] = $visible ? 'block' : 'none';
        $data['courses']=[];
        //loop through the items,massage data and add to table
        //itemname itemid,filename,itemdate, id
        $currentitem=0;
        foreach ($courses as $course) {
            $ditem=[];
            $ditem['id']= $course->courseid;
            $ditem['coursename'] = $course->coursename;
            $ditem['wwwroot'] = $CFG->wwwroot;
            $data['courses'][]=$ditem;
        }
        return $this->render_from_template('block_poodllclassroom/courselist', $data);

    }

    /**
     * No items, thats too bad
     */
    public function no_courses($visible){
        $data=[];
        $data['display'] = $visible ? 'block' : 'none';
        return $this->render_from_template('block_poodllclassroom/nocoursescontainer', $data);
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
        $button = \html_writer::link('#', $buttonlabel, array('class'=>'btn ' . $bootstrapclass . ' ' . $buttonclass,'type'=>'button','id'=>$buttonclass, 'data-id'=>0));
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