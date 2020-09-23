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
        $content = "";
        $containertag = 'createcourse';
        $amodalcontainer = $this->fetch_modalcontainer($title,$content,$containertag);

        $createuserbutton = $this->js_trigger_button('createuser', true,
                get_string('createuser',constants::M_COMP), 'btn-primary');

        $createcoursebutton = $this->js_trigger_button('createcourse', true,
                get_string('createcourse',constants::M_COMP), 'btn-primary');

        //This is all the info we pass to javascript
        //we need to write it to html so we do not clog the JS. ( moodle complains  )
        $schoolinfo = common::get_poodllschool_by_currentuser();
        $schoolplan = common::get_plan_by_currentuser();
        $blockopts=array('modulecssclass' => 'block_poodllclassroom',
                'contextid'=>$contextid,
                'tableid'=>$tableid,
                'schoolinfo'=>$schoolinfo,
                'schoolplan'=>$schoolplan);
        $jsonstring = json_encode($blockopts);
        $propsid = 'propsid_' . \html_writer::random_id();
        $blockopts_html =
                \html_writer::tag('input', '', array('id' => $propsid, 'type' => 'hidden', 'value' => $jsonstring));
        // we tag the html element that we stashed the props in with an id, and just pass that id to js
        //js will pull the props from DOM and recreate the props data object
        $props = array('id'=>$propsid);
        $this->page->requires->js_call_amd(constants::M_COMP . "/blockcontroller", 'init', array($props));

        //mysublink
        $mysublink = $this->create_editmysub_button();

        //changeplan link
        $changeplanlink = $this->create_gotochangeplan_button();
        //initialise content
        $content =  $blockopts_html  . $changeplanlink .  $mysublink  . $createcoursebutton . '<br>';

        $visible=false;
        if($courses) {
            $visible = true;
        }else{
            $courses=[];
        }
        $content .= $this->create_course_list($courses,$visible );
        $content .= $this->no_courses(!$visible);

        $content .=  '<br>' . $createuserbutton . '<br>';

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

    function fetch_changeplan_toppart() {
        $ret = $this->output->heading(get_string('changeplan', constants::M_COMP),3);
        $ret .= \html_writer::div(get_string('changeplaninstructions',constants::M_COMP),constants::M_COMP . '_changeplaninstructions');
        return $ret;

    }

    function fetch_changeplan_buttons(){

        //get plans
        $billingintervals = common::fetch_billingintervals();
        $plans=common::fetch_plans();
        $myschool = common::get_poodllschool_by_currentuser();
        if(!$myschool){
            $ret =  \html_writer::div(get_string('youhavenosubscription',constants::M_COMP),constants::M_COMP . '_nosubscription');
            return $ret;

        }
        $monthlyplans = [];
        $yearlyplans = [];
        $showfirst = constants::M_BILLING_MONTHLY;
        foreach($plans as $plan) {
            $plan->billingintervalname=$billingintervals[$plan->billinginterval];
            //if the users current plan, and its not free/monthly, then set the active display to yeif($plan->id==$myschool->planid){
            if($plan->id == $myschool->planid) {
                $plan->selected = true;
                if ($plan->billinginterval == constants::M_BILLING_YEARLY) {
                    $showfirst = constants::M_BILLING_YEARLY;
                }
            }

            switch($plan->billinginterval){
                case constants::M_BILLING_MONTHLY:
                    $monthlyplans[] = $plan;
                    break;
                case constants::M_BILLING_YEARLY:
                    $yearlyplans[] = $plan;
                    break;
                case constants::M_BILLING_FREE:
                    $monthlyplans[] = $plan;
                    $yearlyplans[] = $plan;
                    break;

            }
        }

        //here we set up any info we need to pass into javascript
        $opts =Array();
        $opts['siteprefix']= get_config(constants::M_COMP,'chargebeesiteprefix');
        $opts['changeplanclass']=constants::M_COMP . '_changeplan';
        $this->page->requires->js_call_amd(constants::M_COMP . "/chargebeehelper", 'init', array($opts));


        //togglebutton
        $togglebutton = \html_writer::link('#',get_string('monthlyyearly',constants::M_COMP),
                array('class' => 'btn btn-secondary monthlyyearly' ));
        $togglediv= \html_writer::div($togglebutton,constants::M_COMP . '_monthlyyearly');

        //monthly plans
        $mdata =array();
        $mdata['plans']=$monthlyplans;
        $mdata['display']=($showfirst==constants::M_BILLING_MONTHLY) ? '' : 'block_poodllclassroom_hidden';
        $mdata['billinginterval']='monthly';
        $monthly = $this->render_from_template('block_poodllclassroom/upgradecontainer', $mdata);

        //yearly plans
        $ydata =array();
        $ydata['plans']=$yearlyplans;
        $ydata['display']=($showfirst==constants::M_BILLING_YEARLY) ? '' : 'block_poodllclassroom_hidden';
        $ydata['billinginterval']='yearly';
        $yearly = $this->render_from_template('block_poodllclassroom/upgradecontainer', $ydata);

        return $togglediv . $monthly . $yearly;

    }


    function create_gotochangeplan_button(){
    global $CFG;

        $link = \html_writer::link($CFG->wwwroot . constants::M_URL . '/subs/changeplan.php',get_string('changeplan',constants::M_COMP),
                array('class' => 'btn btn-secondary '));
        return \html_writer::div($link,constants::M_COMP . '_changeplandiv');

    }

    function create_editmysub_button(){
        global $CFG;
        /*
        $urlparams =array();
        $link = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/accessportal.php', $urlparams),
                $this->output->pix_icon('t/edit', get_string('editmysub',constants::M_COMP)),
                array('title' => get_string('editmysub',constants::M_COMP)));
        return \html_writer::div($link,constants::M_COMP . '_editmysub');
        */

        $link = \html_writer::link($CFG->wwwroot . constants::M_URL . '/subs/accessportal.php',get_string('editmysub',constants::M_COMP),
                array('class' => 'btn btn-secondary '));
        return \html_writer::div($link,constants::M_COMP . '_editmysub');

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


    //return a button that will allow user to add a new sub
    function fetch_addplan_button(){
        $thebutton = new \single_button(
                new \moodle_url(constants::M_URL . '/subs/edit.php',array()),
                get_string('addplan', constants::M_COMP), 'get');
        return $thebutton;
    }

    //Fetch subs table
    function fetch_plans_table($plans){
        global $DB;

        $params=[];
        $baseurl = new \moodle_url(constants::M_URL . '/subs/subs.php', $params);


        //add sub button
        $addbutton = $this->fetch_addplan_button();

        $billingintervals = common::fetch_billingintervals();
        $data = array();
        foreach($plans as $plan) {
            $fields = array();
            $fields[] = $plan->id;
            $fields[] = $plan->name;
            $fields[] = $billingintervals[$plan->billinginterval];
            $fields[] = $plan->maxusers;
            $fields[] = $plan->maxcourses;
            $fields[] = $plan->features;
            $fields[] = $plan->upstreamplan;
            $fields[] = $plan->price;
            $fields[] = $plan->description;

            $buttons = array();

            $urlparams = array('id' => $plan->id,'type'=>'plan','returnurl' => $baseurl->out_as_local_url());


            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php', $urlparams),
                    $this->output->pix_icon('t/edit', get_string('edit')),
                    array('title' => get_string('edit')));

            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php',
                        $urlparams + array('delete' => 1)),
                        $this->output->pix_icon('t/delete', get_string('delete')),
                        array('title' => get_string('delete')));

            $fields[] = implode(' ', $buttons);

            $data[] = $row = new \html_table_row($fields);
        }

        $table = new \html_table();
        $table->head  = array(get_string('id', constants::M_COMP),
                get_string('planname', constants::M_COMP),
                get_string('billinginterval', constants::M_COMP),
                get_string('maxusers', constants::M_COMP),
                get_string('maxcourses', constants::M_COMP),
                get_string('features', constants::M_COMP),
                get_string('upstreamplan', constants::M_COMP),
                get_string('price', constants::M_COMP),
                get_string('description', constants::M_COMP),
                get_string('action'));
        $table->colclasses = array('leftalign name', 'leftalign size','centeralign action');

        $table->id = 'subs';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = $data;

        //return add button and table
        $heading = $this->output->heading('Plans',3);
       return  $heading . $this->render($addbutton) .  \html_writer::table($table);

    }

    //Fetch schools table
    function fetch_schools_table($schools){
        global $DB;

        $params=[];
        $baseurl = new \moodle_url(constants::M_URL . '/subs/subs.php', $params);
        $plans = common::fetch_plans();
        $billingintervals = common::fetch_billingintervals();

        $data = array();
        foreach($schools as $school) {
            $fields = array();
            $fields[] = $school->id;
            $fields[] = $school->schoolname . "($school->companyid)";
            $fields[] = $school->ownerfirstname . ' ' . $school->ownerlastname . "($school->ownerid)";
            $fields[] = $plans[$school->planid]->name  . "($school->planid) " . $billingintervals[$plans[$school->planid]->billinginterval];
            $fields[] = $school->status;
            $fields[] = $school->upstreamsubid;
            $fields[] = $school->upstreamownerid;
            $fields[] = $school->timemodified;

            $buttons = array();

            $urlparams = array('id' => $school->id,'type'=>'school','returnurl' => $baseurl->out_as_local_url());


            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php', $urlparams),
                    $this->output->pix_icon('t/edit', get_string('edit')),
                    array('title' => get_string('edit')));

            /* remove delete option for now */
            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php',
                    $urlparams + array('delete' => 1)),
                    $this->output->pix_icon('t/delete', get_string('delete')),
                    array('title' => get_string('delete')));


            $fields[] = implode(' ', $buttons);

            $data[] = $row = new \html_table_row($fields);
        }

        $table = new \html_table();
        $table->head  = array(get_string('id', constants::M_COMP),
                get_string('company', constants::M_COMP),
                get_string('owner', constants::M_COMP),
                get_string('plan', constants::M_COMP),
                get_string('status', constants::M_COMP),
                get_string('upstreamsubid', constants::M_COMP),
                get_string('upstreamownerid', constants::M_COMP),
                get_string('lastchange', constants::M_COMP),
                get_string('action'));
        $table->colclasses = array('leftalign name', 'leftalign size','centeralign action');

        $table->id = 'subs';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = $data;

        //return add button and table
        $heading = $this->output->heading('Schools',3);
        return   $heading . \html_writer::table($table);

    }
}