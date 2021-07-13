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


    function fetch_normalpeople_block_content(){
        $content = \html_writer::tag('h5','fakename');
        return $content;
    }

    function fetch_block_content($context, $users=false, $courses=false){
        global $CFG;

        //is this right?
        $context = \context_system::instance();

        //init content
        $content ='';

        //init options thingy
        $optionsdata=array();
        $options=array();

        //super admin can manage everything from super admin page
        if(has_capability('block/poodllclassroom:manageintegration', $context)){
            $options[]=array('url'=>$CFG->wwwroot . '/blocks/poodllclassroom/subs/subs.php',
                    'label'=>get_string('superadminarea',constants::M_COMP));
        }

        //if we are a reseller we are showing reseller things and reseller options
        $me_reseller = common::fetch_me_reseller();
        if($me_reseller){
            $portalurl= common::get_portalurl_by_upstreamid($me_reseller->upstreamuserid);
            $options[] = array('url' => $portalurl,
                    'label' => get_string('managesubscriptions', constants::M_COMP));

            //Build options widget
            $optionsdata['options']=$options;
            $optionsdropdown = $this->render_from_template('block_poodllclassroom/optionsdropdown', $optionsdata);
            $content .=  $optionsdropdown;

            //display schools
            $resold_schools = common::fetch_schools_by_reseller($me_reseller->id);
            $schoolstable = $this->fetch_schools_table($resold_schools);
            $content .=  $schoolstable;

            return $content;

        //not reseller
        }else{

            $school = common::get_poodllschool_by_currentuser();
            $portalurl = common::get_portalurl_by_upstreamid($school->upstreamownerid);
            $options[] = array('url' => $portalurl,
                    'label' => get_string('managesubscriptions', constants::M_COMP));

            //if not reseller we just have one school, so we can edit it
            $options[] = array('url' => $CFG->wwwroot . '/blocks/poodllclassroom/subs/editmyschool.php?id='. $school->id,
                    'label' => get_string('editmyschool', constants::M_COMP));

            //Build options widget
            $optionsdata['options']=$options;
            $optionsdropdown = $this->render_from_template('block_poodllclassroom/optionsdropdown', $optionsdata);
            $content .=  $optionsdropdown;
        }





        //Gather subs info
        $subs = common::get_poodllsubs_by_currentuser();
        $extended_subs = common::get_extended_sub_data($subs);
        $display_subs = common::get_display_sub_data($extended_subs);


        //subs section
        $subssectiondata = array('subs'=>array_values($display_subs));
        $content .= $this->render_from_template('block_poodllclassroom/subsheader',$subssectiondata);

        //Platform Subs Details Section
        $moodlesubs=[];
        $ltisubs=[];
        $classroomsubs=[];
        foreach ($display_subs as $dsub){

            switch($dsub->plan->platform){
                case constants::M_PLATFORM_MOODLE:
                    $moodlesubs[] = $dsub;
                    break;

                case constants::M_PLATFORM_LTI:
                        $ltisubs[] = $dsub;
                        break;
                case constants::M_PLATFORM_CLASSROOM:
                    $classroomsubs[] = $dsub;
                    break;
            }
        }

        //Platform Moodle Subs Section
        $editschoolurl =  $CFG->wwwroot . '/blocks/poodllclassroom/subs/editmyschool.php?id='. $school->id;
        if(count($moodlesubs)>0){
            $content .= $this->render_from_template('block_poodllclassroom/moodlesubs',
                    ['school'=>$moodlesubs[0]->school,'subs'=>$moodlesubs, 'editschoolurl'=>$editschoolurl]);
        }

        //Platform LTI Section
        if(count($ltisubs)>0){
            $content .= $this->render_from_template('block_poodllclassroom/ltisubs',['subs'=>$ltisubs]);
        }

        //Platform Classroom Section
        if(count($classroomsubs)>0){
            $content .= $this->render_from_template('block_poodllclassroom/classroomsubs',['subs'=>$classroomsubs]);
        }



        return $content;

        //this is the old create-manage course / user code

        $contextid = $context->id;
        $content = "";

        //userlist
        //if we have items, show em. Data tables will make it pretty
        //Prepare datatable(before header printed)
        $usertableid = '' . constants::M_CLASS_USERLIST . '_' . '_opts_9999';
        $this->setup_datatables($usertableid,count($users));

        //This is all the info we pass to javascript
        //we need to write it to html so we do not clog the JS. ( moodle complains  )
        $subsinfo = common::get_poodllsubs_by_currentuser();
        if(!$subsinfo){
            $subinfo = '';
        }else{
            $subinfo= array_shift($subsinfo);
        }
        $schoolplan = common::get_plan($subinfo->planid);
        $blockopts=array('modulecssclass' => 'block_poodllclassroom',
                'contextid'=>$contextid,
                'tableid'=>$usertableid,
                'subinfo'=>$subinfo,
                'schoolplan'=>$schoolplan);
        $jsonstring = json_encode($blockopts);
        $propsid = 'propsid_' . \html_writer::random_id();
        $blockopts_html =
                \html_writer::tag('input', '', array('id' => $propsid, 'type' => 'hidden', 'value' => $jsonstring));
        // we tag the html element that we stashed the props in with an id, and just pass that id to js
        //js will pull the props from DOM and recreate the props data object
        $props = array('id'=>$propsid);
        $this->page->requires->js_call_amd(constants::M_COMP . "/blockcontroller", 'init', array($props));



        $title = get_string('createcourse',constants::M_COMP);

        $containertag = 'createcourse';
        $amodalcontainer = $this->fetch_modalcontainer($title,$content,$containertag);

        $createuserbutton = $this->js_trigger_button('createuser', true,
                get_string('createuserstart',constants::M_COMP), 'btn-primary');

        $uploaduserbutton = $this->js_trigger_button('uploaduser', true,
                get_string('uploaduserstart',constants::M_COMP), 'btn-primary');

        $createcoursebutton = $this->js_trigger_button('createcourse', true,
                get_string('createcoursestart',constants::M_COMP), 'btn-primary');

        //courses section
        $coursesectiondata=array('label'=>get_string('courses',constants::M_COMP));
        $content .= $this->render_from_template('block_poodllclassroom/sectionheader', $coursesectiondata);
        $content .=  $maxcourseslabel . $createcoursebutton . '<br>';

        $courselistvisible=false;
        if($courses) {
            $courselistvisible = true;
        }else{
            $courses=[];
        }
        $content .= $this->create_course_list($courses,$courselistvisible);
        $content .= $this->no_courses(!$courselistvisible);
        $content .= '<hr>';

        //user section
        $usersectiondata=array('label'=>get_string('users',constants::M_COMP));
        $content .= $this->render_from_template('block_poodllclassroom/sectionheader', $usersectiondata);
        $userheaderdata=array('maxuserslabel'=>$maxuserslabel,'createuserbutton'=>$createuserbutton,'uploaduserbutton'=>$uploaduserbutton );
        $content .= $this->render_from_template('block_poodllclassroom/userheader', $userheaderdata);


        $userlistvisible=false;
        if($users) {
            $userlistvisible = true;
        }else{
            $users=[];
        }

        $content .= $this->create_user_list($users,$usertableid, $userlistvisible);
        $content .= $this->no_users(! $userlistvisible);

        //max labels
        $maxusers = $schoolplan ? $schoolplan->maxusers : 0;
        $maxcourses = $schoolplan ? $schoolplan->maxcourses : 0;
        $maxcourseslabel = get_string('maximumcourses',constants::M_COMP,$maxcourses);
        $maxuserslabel = get_string('maximumusers',constants::M_COMP,$maxusers);

        //initialise content
        $content =  $blockopts_html  . $optionsdropdown;

         $content .= $amodalcontainer;
         return $content;

    }

    function fetch_changeplan_toppart() {
        $ret = $this->output->heading(get_string('changeplan', constants::M_COMP),3);
        $ret .= \html_writer::div(get_string('changeplaninstructions',constants::M_COMP),constants::M_COMP . '_changeplaninstructions');
        return $ret;

    }

    function fetch_changeplan_buttons($extendedsub){

        if(!$extendedsub){
            $ret =  \html_writer::div(get_string('youhavenosubscription',constants::M_COMP),constants::M_COMP . '_nosubscription');
            return $ret;

        }

        //get plans
        $billingintervals = common::fetch_billingintervals();
        $plans=common::fetch_plans_by_family($extendedsub->plan->planfamily);

        $usercount=0;
        $coursecount=0;

        $monthlyplans = [];
        $yearlyplans = [];
        $showfirst = constants::M_BILLING_MONTHLY;

        foreach($plans as $plan) {
            $plan->billingintervalname=$billingintervals[$plan->billinginterval];
            //if the users current plan, and its not free/monthly, then set the active display to yeif($plan->id==$myschool->planid){
            if($plan->id == $extendedsub->planid) {
                $plan->selected = true;
                $plan->disabled = true;
                if ($plan->billinginterval == constants::M_BILLING_YEARLY) {
                    $showfirst = constants::M_BILLING_YEARLY;
                }
            }else{
                if($plan->maxusers < $usercount){
                    $plan->disabled = true;
                    $plan->toomanyusers = true;
                    $plan->usercount = $usercount;
                }
                if($plan->maxcourses < $coursecount){
                    $plan->disabled = true;
                    $plan->toomanycourses = true;
                    $plan->coursecount = $coursecount;
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
        $mdata['billingintervallabel']=get_string('monthly',constants::M_COMP);
        $monthly = $this->render_from_template('block_poodllclassroom/upgradecontainer', $mdata);

        //yearly plans
        $ydata =array();
        $ydata['plans']=$yearlyplans;
        $ydata['display']=($showfirst==constants::M_BILLING_YEARLY) ? '' : 'block_poodllclassroom_hidden';
        $ydata['billinginterval']='yearly';
        $ydata['billingintervallabel']=get_string('yearly',constants::M_COMP);
        $yearly = $this->render_from_template('block_poodllclassroom/upgradecontainer', $ydata);

        return $togglediv . $monthly . $yearly;

    }



    function create_user_list($users,$tableid,$visible){
        global $USER;

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
            if($user->id ==$USER->id){
                $ditem['isme'] = true;
            }
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
        $data['display'] = $visible ? '' : 'block_poodllclassroom_hidden';
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
        $data['display'] = $visible ? '' : 'block_poodllclassroom_hidden';
        return $this->render_from_template('block_poodllclassroom/nocoursescontainer', $data);
    }

    function setup_datatables($tableid, $usercount=0){
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

        if($usercount < 5){
            $tableprops['searching']=false;
            $tableprops['paging']=false;
        }

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
            $fields[] = $plan->planfamily;
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
                get_string('planfamily', constants::M_COMP),
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

    //return a button that will allow user to add a new sub
    function fetch_addsub_button(){
        $thebutton = new \single_button(
            new \moodle_url(constants::M_URL . '/subs/edit.php',array('type'=>'sub')),
            get_string('addsub', constants::M_COMP), 'get');
        return $thebutton;
    }

    //return a button that will allow user to add a new school
    function fetch_addschool_button(){
        $thebutton = new \single_button(
                new \moodle_url(constants::M_URL . '/subs/edit.php',array('type'=>'school')),
                get_string('addschool', constants::M_COMP), 'get');
        return $thebutton;
    }
    function fetch_addreseller_button(){
        $thebutton = new \single_button(
                new \moodle_url(constants::M_URL . '/subs/edit.php',array('type'=>'reseller')),
                get_string('addreseller', constants::M_COMP), 'get');
        return $thebutton;
    }


    //Fetch subs table
    function fetch_subs_table($subs){
        global $DB;

        $params=[];
        $baseurl = new \moodle_url(constants::M_URL . '/subs/subs.php', $params);
        $plans = common::fetch_plans();
        $billingintervals = common::fetch_billingintervals();

        //add sub button
        $addbutton = $this->fetch_addsub_button();

        $data = array();
        foreach($subs as $sub) {
            $fields = array();
            $fields[] = $sub->id;
            $fields[] = $sub->schoolname;
            $fields[] = $plans[$sub->planid]->name  . "($sub->planid) " . $billingintervals[$plans[$sub->planid]->billinginterval];
            $fields[] = $sub->upstreamsubid;
            $fields[] = $sub->status;
            $fields[] = $sub->jsonfields;
            $fields[] = strftime('%d %b %Y', $sub->timemodified);

            $buttons = array();

            $urlparams = array('id' => $sub->id,'type'=>'sub','returnurl' => $baseurl->out_as_local_url());


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
                get_string('school', constants::M_COMP),
                get_string('plan', constants::M_COMP),
                get_string('upstreamsubid', constants::M_COMP),
                get_string('status', constants::M_COMP),
                get_string('jsonfields', constants::M_COMP),
                get_string('lastchange', constants::M_COMP),
                get_string('action'));
        $table->colclasses = array('leftalign name', 'leftalign size','centeralign action');

        $table->id = 'subs';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = $data;

        //return add button and table
        $heading = $this->output->heading('Subs',3);
        return   $heading  . $this->render($addbutton) .  \html_writer::table($table);

    }

    //Fetch schools table
    function fetch_schools_table($schools){
        global $DB;

        $params=[];
        $baseurl = new \moodle_url(constants::M_URL . '/subs/subs.php', $params);

        //add school button
        $addbutton = $this->fetch_addschool_button();

        $data = array();
        foreach($schools as $school) {
            $fields = array();
            $fields[] = $school->id;
            $fields[] = $school->name ;
            $fields[] = $school->ownerfirstname . ' ' . $school->ownerlastname . "($school->ownerid)";
            $fields[] = $school->upstreamownerid;
            $fields[] = $school->status;
            $fields[] = $school->jsonfields;
            $fields[] = strftime('%d %b %Y', $school->timemodified);

            $buttons = array();

            $urlparams = array('id' => $school->id,'type'=>'school','returnurl' => $baseurl->out_as_local_url());

            //view school subs and other details
            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/schooldetails.php', $urlparams),
                    $this->output->pix_icon('t/preview', get_string('view')),
                    array('title' => get_string('view')));

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
                get_string('school', constants::M_COMP),
                get_string('owner', constants::M_COMP),
                get_string('upstreamownerid', constants::M_COMP),
                get_string('status', constants::M_COMP),
                get_string('jsonfields', constants::M_COMP),
                get_string('lastchange', constants::M_COMP),
                get_string('action'));
        $table->colclasses = array('leftalign name', 'leftalign size','centeralign action');

        $table->id = 'schools';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = $data;

        //return add button and table
        $heading = $this->output->heading('Schools',3);
        return   $heading  . $this->render($addbutton) .  \html_writer::table($table);

    }

    //Fetch resellers table
    function fetch_resellers_table($resellers){
        global $DB;

        $params=[];
        $baseurl = new \moodle_url(constants::M_URL . '/subs/subs.php', $params);

        //add school button
        $addbutton = $this->fetch_addreseller_button();

        $data = array();
        foreach($resellers as $reseller) {
            $fields = array();
            $fields[] = $reseller->id;
            $fields[] = $reseller->name ;
            $fields[] = $reseller->userid;
            $fields[] = $reseller->status;
            $fields[] = $reseller->jsonfields;
            $fields[] = strftime('%d %b %Y', $reseller->timemodified);

            $buttons = array();

            $urlparams = array('id' => $reseller->id,'type'=>'reseller','returnurl' => $baseurl->out_as_local_url());


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
                get_string('reseller', constants::M_COMP),
                get_string('user', constants::M_COMP),
                get_string('status', constants::M_COMP),
                get_string('jsonfields', constants::M_COMP),
                get_string('lastchange', constants::M_COMP),
                get_string('action'));
        $table->colclasses = array('leftalign name', 'leftalign size','centeralign action');

        $table->id = 'schools';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = $data;

        //return add button and table
        $heading = $this->output->heading('Resellers',3);
        return   $heading  . $this->render($addbutton) .  \html_writer::table($table);

    }
}