<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/26
 * Time: 13:16
 */

namespace block_poodllclassroom\output;

use block_poodllclassroom\constants;
use block_poodllclassroom\common;
use block_poodllclassroom\chargebee_helper;
use block_poodllclassroom\cpapi_helper;


class renderer extends \plugin_renderer_base {


    /**
     * Return HTML to display limited header
     */
    public function header() {
        return $this->output->header();
    }

    function fetch_block_content($context, $users=false, $courses=false) {
        global $CFG, $USER;

        // is this right?
        $context = \context_system::instance();

        // init content
        $content = '';

        // init options thingy
        $optionsdata = [];
        $options = [];

        // super admin can manage everything from super admin page
        if(has_capability('block/poodllclassroom:manageintegration', $context)){
            $options[] = ['url' => $CFG->wwwroot . '/blocks/poodllclassroom/subs/subs.php',
                    'label' => get_string('superadminarea', constants::M_COMP)];
            $options[] = ['url' => $CFG->wwwroot . '/blocks/poodllclassroom/subs/userreports.php',
                'label' => get_string('renewerreport', constants::M_COMP)];
        }

        // set up js for dropdown options
        // here we set up any info we need to pass into javascript
        $opts = [];
        $opts['siteprefix'] = get_config(constants::M_COMP, 'chargebeesiteprefix');
        $opts['newplanclass'] = constants::M_COMP . '_newplan';
        $opts['gocbcheckoutclass'] = constants::M_COMP . '_gocbcheckout';
        $this->page->requires->js_call_amd(constants::M_COMP . "/chargebeehelper", 'init', [$opts]);
        // clipboard copy thingy
        $clipboardopts = [];
        $this->page->requires->js_call_amd(constants::M_COMP . "/clipboardhelper", 'init', [$clipboardopts]);

        // set up our lightbox css (for the watch this video mustache template called from moodlesubs mustache and lti subs mustache)
        $this->page->requires->css(new \moodle_url("https://cdn.jsdelivr.net/npm/@fancyapps/ui@4.0/dist/fancybox.css"));

        // if we are a reseller we are showing reseller things and reseller options
        $mereseller = common::fetch_me_reseller();
        if($mereseller && $mereseller->resellertype == constants::M_RESELLER_THIRDPARTY){
            // fetch reseller header
            $resellerheader = $this->render_from_template('block_poodllclassroom/resellerheader', $mereseller);
            $content .= $resellerheader;
            $subs = common::fetch_subs_by_user($mereseller->userid);
            if($subs) {
                $billingaccount = ['url' => '#', 'cbaction' => 'ssp', 'upstreamownerid' => $mereseller->upstreamuserid, 'type' => 'billingaccount', 'label' => get_string('billingaccount', constants::M_COMP)];
                $billinghistory = ['url' => '#', 'cbaction' => 'ssp', 'upstreamownerid' => $mereseller->upstreamuserid, 'type' => 'billinghistory', 'label' => get_string('billinghistory', constants::M_COMP)];
                $paymentsources = ['url' => '#', 'cbaction' => 'ssp', 'upstreamownerid' => $mereseller->upstreamuserid, 'type' => 'paymentsources', 'label' => get_string('paymentsources', constants::M_COMP)];
                $optionsdata['billingaccount'] = $billingaccount;
                $optionsdata['billinghistory'] = $billinghistory;
                $optionsdata['paymentsources'] = $paymentsources;

                $options[] = $billingaccount;
                $options[] = $billinghistory;
                $options[] = $paymentsources;
            }
            // $portalurl= chargebee::get_portalurl_by_upstreamid($me_reseller->upstreamuserid);
            // $options[] = array('url' => $portalurl, 'label' => get_string('managesubscriptions', constants::M_COMP));

            // set account name
            $optionsdata['useraccountname'] = $mereseller->name;

            // Build options widget
            $optionsdata['options'] = $options;
            $optionsdropdown = $this->render_from_template('block_poodllclassroom/optionsdropdown', $optionsdata);
            $content .= $optionsdropdown;

            // display header
            $content .= $this->render_from_template('block_poodllclassroom/resellerheading', $mereseller);

            // display schools
            $resoldschools = common::fetch_schools_by_reseller($mereseller->id);
            $resoldschools = common::add_expiring_sub_to_schools($resoldschools);
            $params = [];
            $returnurl = new \moodle_url( $CFG->wwwroot . '/my/', $params);
            $schoolstable = $this->fetch_schools_table($resoldschools, $returnurl);
            $content .= $schoolstable;

            return $content;

            // not reseller
        }else{

            $school = common::get_poodllschool_by_currentuser();
            // if no school, we better make one quick
            if(!$school) {
                $school = common::create_blank_school();
            }

            // if we do not have a school we can not show subs or school options
            if($school) {
                $school->options = $options;
                $content .= $this->render_from_template('block_poodllclassroom/schoolheader', $school);
                // if not reseller we just have one school, so we can edit it
                /*
                $options[] = array('url' => $CFG->wwwroot . '/blocks/poodllclassroom/subs/editmyschool.php?id=' . $school->id,
                    'label' => get_string('editmyschool', constants::M_COMP));
                */

                // set the accountname to the school name
                $optionsdata['useraccountname'] = $school->name;

                $subs = common::fetch_subs_by_school($school->id);
                if($subs) {
                    // We have account management buttons ($optionsdata) as well as the dropdown options ($options). Users dont see dropdowns. So year
                    $billingaccount = ['url' => '#', 'cbaction' => 'ssp', 'upstreamownerid' => $school->upstreamownerid, 'type' => 'billingaccount', 'label' => get_string('billingaccount', constants::M_COMP)];
                    $billinghistory = ['url' => '#', 'cbaction' => 'ssp', 'upstreamownerid' => $school->upstreamownerid, 'type' => 'billinghistory', 'label' => get_string('billinghistory', constants::M_COMP)];
                    $paymentsources = ['url' => '#', 'cbaction' => 'ssp', 'upstreamownerid' => $school->upstreamownerid, 'type' => 'paymentsources', 'label' => get_string('paymentsources', constants::M_COMP)];
                    $optionsdata['billingaccount'] = $billingaccount;
                    $optionsdata['billinghistory'] = $billinghistory;
                    $optionsdata['paymentsources'] = $paymentsources;

                    $options[] = $billingaccount;
                    $options[] = $billinghistory;
                    $options[] = $paymentsources;
                }else{

                    // if they are not a reseller and they have no subscription,
                    // //then lets encourage them to take a subscription from the subscriptions page
                    // the only reseller that should arrive here is Poodll admin, and we also do not want to be redirected, so lets first check
                    if(!$mereseller) {
                        $checkouturl = new \moodle_url(constants::M_URL . '/subs/checkout.php',
                            ['schoolid' => $school->id, 'platform' => constants::M_PLATFORM_MOODLE, 'planfamily' => 'all']);
                        if(!$this->page->headerprinted){
                            redirect($checkouturl, get_string('accountisready', constants::M_COMP), 3);

                            // in this case we can not safely redirect because the header has been printed
                            // wo we issue a js redirect
                            /*
                            $redirectdata = new \stdClass();
                            $redirectdata->label = get_string('getfirstsubscription', constants::M_COMP);
                            $redirectdata->url = $checkouturl->out();
                            $content = $this->render_from_template('block_poodllclassroom/jsredirect',$redirectdata);
                            return $content;
                            */
                        }else{
                            redirect($checkouturl, get_string('redirecting', constants::M_COMP), 3);
                        }

                    }

                    // free trial button ??
                    /*
                    $freetrialdata=[];
                    $freetrialdata['planid']=59;
                    $freetrialdata['schoolid']=$school->id;
                    $freetrialdata['currency']='USD';
                    $freetrialdata['billinginterval']=2;
                    $freetrialbutton = $this->render_from_template('block_poodllclassroom/dashfreetrialbutton', $freetrialdata);
                    $content .= $freetrialbutton;
                    */

                }
                // manage all our subscriptions
                // $portalurl =chargebee::get_portalurl_by_upstreamid($school->upstreamownerid);
                // $options[] = array('url' => $portalurl,'label' => get_string('managesubscriptions', constants::M_COMP));

            }

            // Build options widget
            $optionsdata['options'] = $options;

            $optionsdropdown = $this->render_from_template('block_poodllclassroom/optionsdropdown', $optionsdata);
            $content .= $optionsdropdown;

        }

        // get checkout url
        $checkouturl = new \moodle_url(constants::M_URL . '/subs/checkout.php',
            ['schoolid' => $school->id, 'platform' => constants::M_PLATFORM_MOODLE, 'planfamily' => 'all']);

        // Gather subs info
        $subs = common::fetch_subs_by_user($USER->id);  // common::get_poodllsubs_by_currentuser();
        $extendedsubs = common::get_extended_sub_data($subs);
        $displaysubs = common::get_display_sub_data($extendedsubs);

        // subs section
        $subssectiondata = ['subs' => array_values($displaysubs), 'checkouturl' => $checkouturl->out()];
        if(count($subssectiondata['subs']) < 1){
            $subssectiondata['nosubs'] = true;
        }

        // checkout button
        // $checkoutbuttondata = ['school'=>$school,'planfamily'=>'all', 'platform'=>constants::M_PLATFORM_MOODLE, 'checkouturl'=>$checkouturl->out()];
        // $checkoutbutton = $this->render_from_template('block_poodllclassroom/checkoutpagebutton', $checkoutbuttondata);

        $subssectiondata['show_expiretime'] = true;
        $subssectiondata['show_payment'] = true;
        $subssectiondata['show_status'] = true;
        // $subssectiondata['checkoutbutton']=$checkoutbutton;
        $content .= $this->render_from_template('block_poodllclassroom/subsheader', $subssectiondata);

        // Platform Subs Details Section
        $moodlesubs = [];
        $ltisubs = [];
        $classroomsubs = [];
        foreach ($displaysubs as $dsub){

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

        // Platform Moodle Subs Section
        $editschoolurl = $CFG->wwwroot . '/blocks/poodllclassroom/subs/editmyschool.php?id='. $school->id;
        $moodledata = ['school' => $school, 'subs' => $moodlesubs, 'editschoolurl' => $editschoolurl,
            'platform' => constants::M_PLATFORM_MOODLE,
            'planfamily' => 'all',
            'checkouturl' => $checkouturl->out(),
            'videoid' => '694802997',
            'options' => $options];
        if(count($moodlesubs) > 0){
            $moodledata['hassubs'] = true;
            $moodledata['school'] = $moodlesubs[0]->school;
            $rawusagedata = cpapi_helper::fetch_usage_data($moodlesubs[0]->school->apiuser);
            if ($rawusagedata) {
                $reportdata = \block_poodllclassroom\common::compile_report_data($rawusagedata);
                $moodledata['usagereport'] = $this->display_usage_report($reportdata, $rawusagedata);
            } else {
                $moodledata['usagereport'] = get_string('nousagedata', constants::M_COMP);
            }
        }
        $content .= $this->render_from_template('block_poodllclassroom/moodlesubs',
                $moodledata);

        // Platform LTI Section
        $enablelti = get_config(constants::M_COMP, 'enablelti');
        if($enablelti) {
            $ltidata = ['school' => $school, 'subs' => $ltisubs,
                'planfamily' => 'all',
                'platform' => constants::M_PLATFORM_LTI,
                'checkouturl' => $checkouturl->out()];
            if (count($ltisubs) > 0) {
                $ltidata['hassubs'] = true;
                $ltidata['school'] = $ltisubs[0]->school;
            }

            // lti tutorials
            $ltitutdata = [];
            $ltitutdata['ltitut'] = [];
            // Moodle
            $ltitutdata['ltitut'][] = ['title' => 'Moodle', 'logourl' => $CFG->wwwroot . constants::M_URL . '/pix/moodle_logo_small.svg',
                'instructions' => get_string('ltimoodleinstructions', constants::M_COMP),
                'tuturl' => 'https://support.poodll.com/en/support/solutions/articles/19000125439-setting-up-poodll-lti-on-your-moodle-site',
                'videoid' => '532567654', 'videobutton' => true];
            // canvas
            $ltitutdata['ltitut'][] = ['title' => 'Canvas', 'logourl' => $CFG->wwwroot . constants::M_URL . '/pix/canvas-transparent.png',
                'instructions' => get_string('lticanvasinstructions', constants::M_COMP),
                'tuturl' => 'https://support.poodll.com/en/support/solutions/articles/19000125450-setting-up-poodll-lti-on-canvas-lms'];
            $ltidata['ltituts'] = $ltitutdata;

            $content .= $this->render_from_template('block_poodllclassroom/ltisubs',
                $ltidata);
        }

        // Platform Classroom Section
        $enableclassroom = get_config(constants::M_COMP, 'enableclassroom');
        if($enableclassroom) {
            if (count($classroomsubs) > 0) {
                $classroomdata = ['school' => $school, 'subs' => $classroomsubs,
                    'planfamily' => 'all',
                    'platform' => constants::M_PLATFORM_CLASSROOM,
                    'checkouturl' => $checkouturl->out()];
                if (count($classroomsubs) > 0) {
                    $classroomdata['hassubs'] = true;
                    $classroomdata['school'] = $classroomsubs[0]->school;
                }
                $content .= $this->render_from_template('block_poodllclassroom/classroomsubs',
                    $classroomdata);
            }
        }
        return $content;
    }

    function fetch_checkout_toppart($school, $platform, $planfamily) {
        global $CFG;

        $ret = '';
        // $ret = $this->output->heading(get_string('checkouttitle', constants::M_COMP),3);
        // $ret .= \html_writer::div(get_string('checkoutinstructions',constants::M_COMP),constants::M_COMP . '_checkoutinstructions');

        $tabsdata = [];
        if($school && isset($school->resold)){
            $returnurl = new \moodle_url(constants::M_URL . '/subs/schooldetails.php', ['id' => $school->id , 'type' => 'school', 'resellerid' => $school->resellerid]);
        }else{
            $returnurl = new \moodle_url($CFG->wwwroot . '/my/');
        }
        $tabsdata['returnurl'] = $returnurl->out();
        $checkouturl = new \moodle_url(constants::M_URL . '/subs/checkout.php', ['schoolid' => $school->id , 'planfamily' => $planfamily]);
        $tabsdata['checkouturl'] = $checkouturl->out();
        $tabsdata['platform_' . strtoupper($platform)] = 1;
        // $tabsdata['school']=$school;
        // $tabsdata['planfamily']=$planfamily;
        $ret .= $this->render_from_template('block_poodllclassroom/checkoutpagetabs', $tabsdata);
        return $ret;
    }

    function fetch_checkout_buttons($school, $platform, $planfamily, $checkoutexisting=false, $existingsubid=false) {
        if (!$school) {
            $ret = \html_writer::div(get_string('youhavenoschool', constants::M_COMP), constants::M_COMP . '_noschool');
            return $ret;
        }
        $platform = strtoupper($platform);
        $planfamily = strtoupper($planfamily);

        // get plans
        $billingintervals = common::fetch_billingintervals();
        $billingintervals[constants::M_BILLING_YEARLY] = get_string('year', constants::M_COMP);
        $onlyvisibleplans = true;
        $plans = common::fetch_plans_by_platform($platform, $planfamily, $onlyvisibleplans);

        // get subs for the school
        // if they have a sub already, they can no longer take the free tria;\l
        $subs = common::fetch_subs_by_school($school->id);
        if($subs && count($subs) > 0) {
            $freeplanavailable = false;
        }else{
            // for now
            $freeplanavailable = true;
        }

        $monthlyplans = [];
        $yearlyplans = [];
        $dailyplans = [];
        $freeplans = [];
        $showfirst = constants::M_BILLING_YEARLY;

        if($existingsubid){
            $thesub = common::fetch_extended_sub($existingsubid);
            $theplanid = $thesub->planid;
        }else{
            $theplanid = false;
        }

        foreach ($plans as $plan) {
            $plan->billingintervalname = $billingintervals[$plan->billinginterval];
            $plan->schoolid = $school->id;
            if($theplanid && $plan->id == $theplanid){
                $plan->selected = true;
            }
            // if the users current plan, and its not free/monthly, then set the active display to yes if($plan->id==$myschool->planid){
            switch ($plan->billinginterval) {
                case constants::M_BILLING_MONTHLY:
                    $monthlyplans[] = $plan;
                    break;
                case constants::M_BILLING_YEARLY:
                    $yearlyplans[] = $plan;
                    break;
                case constants::M_BILLING_DAILY:
                    $dailyplans[] = $plan;
                    break;

            }
            if($plan->hasfreetrial){
                $freeplans[] = $plan;
            }
        }

        // here we set up any info we need to pass into javascript
        $opts = [];
        $opts['siteprefix'] = get_config(constants::M_COMP, 'chargebeesiteprefix');
        // $opts['newplanclass'] = constants::M_COMP . '_newplan';
        $opts['gocbcheckoutclass'] = constants::M_COMP . '_gocbcheckout';
        $this->page->requires->js_call_amd(constants::M_COMP . "/chargebeehelper", 'init', [$opts]);

        // toggle monthly/yearly button
        if (count($monthlyplans) > 0){
            /*
            $togglebutton = \html_writer::link('#', get_string('monthlyyearly', constants::M_COMP),
                array('class' => 'btn btn-secondary monthlyyearly'));
            $togglediv = \html_writer::div($togglebutton, constants::M_COMP . '_monthlyyearly');
            */
            $togglediv = $this->render_from_template('block_poodllclassroom/checkoutbillingintervaltoggle', []);
        }else{
            $togglediv = '';
        }

        // free plans
        $mdata = [];
        foreach($freeplans as $freeplan){
            foreach($subs as $sub){
                if($freeplan->id == $sub->planid){
                    $freeplan->alreadytaken = true;
                    $freeplanavailable = false;
                }
            }
        }
        // this is a little hack, because in the monthly/yearly it used the plan id of the current sub (bad)
        $freeplanid = $freeplan->id;
        $freeplanbillinginterval = $freeplan->billinginterval;
        $mdata['freeplanbillinginterval'] = $freeplanbillinginterval;

        $mdata['plans'] = $freeplans;
        $mdata['freeplanid'] = $freeplanid;
        $mdata['display'] = '';
        $mdata['billinginterval'] = 'Monthly';
        $mdata['currency'] = 'USD';
        $mdata['billingintervallabel'] = get_string('freetrial', constants::M_COMP);
        $mdata['platform_'.constants::M_PLATFORM_MOODLE] = strtoupper($platform) == constants::M_PLATFORM_MOODLE;
        $mdata['platform_'.constants::M_PLATFORM_LTI] = strtoupper($platform) == constants::M_PLATFORM_LTI;
        if($checkoutexisting || !$freeplanavailable) {
            $freely = '';
        }else{
            $freely = $this->render_from_template('block_poodllclassroom/freeplancontainer', $mdata);
        }

        // monthly plans
        $mdata = [];
        $mdata['display'] = 'block_poodllclassroom_hidden';
        $mdata['billinginterval'] = 'Monthly';
        $mdata['currency'] = 'USD';
        $mdata['billingintervallabel'] = get_string('monthly', constants::M_COMP);
        if($checkoutexisting) {
            $mdata['checkoutexisting'] = $checkoutexisting;
            $mdata['currentsubid'] = $existingsubid;
        }
        if($platform == constants::M_PLATFORM_MOODLE && $planfamily == 'ALL'){
            $langplans = [];
            $mediaplans = [];
            $essentialsplans = [];
            $englishcentralplans = [];
            foreach($monthlyplans as $theplan){
                switch($theplan->planfamily){
                    case constants::M_FAMILY_LANG:
                        $theplan->display = $theplan->upstreamplan == 'Poodll-Languages-Lite' ? '' : 'block_poodllclassroom_hidden';
                        $langplans[] = $theplan;
                        break;
                    case constants::M_FAMILY_MEDIA:
                        $theplan->display = $theplan->upstreamplan == 'Poodll-Media-Lite' ? '' : 'block_poodllclassroom_hidden';
                        $mediaplans[] = $theplan;
                        break;
                    case constants::M_FAMILY_ESSENTIALS:
                        $theplan->display = $theplan->upstreamplan == 'Poodll-Essentials-Lite' ? '' : 'block_poodllclassroom_hidden';
                        $essentialsplans[] = $theplan;
                        break;
                    case constants::M_FAMILY_EC:
                        $theplan->display = '';
                        $englishcentralplans[] = $theplan;
                        break;
                }
            }
            if(count($mediaplans) > 0){$mdata['mediaplans'] = $mediaplans;
            }
            if(count($langplans) > 0){$mdata['langplans'] = $langplans;
            }
            if(count($essentialsplans) > 0){$mdata['essentialsplans'] = $essentialsplans;
            }
            if(count($englishcentralplans) > 0){$mdata['englishcentralplans'] = $englishcentralplans;
            }
            $mdata['freeplanavailable'] = $freeplanavailable;
            $mdata['freeplanid'] = $freeplanid;
            $mdata['freeplanbillinginterval'] = $freeplanbillinginterval;
            $monthly = $this->render_from_template('block_poodllclassroom/moodleplanscontainer', $mdata);
        }else {
            $mdata['plans'] = $monthlyplans;
            if($checkoutexisting) {
                $mdata['checkoutexisting'] = $checkoutexisting;
                $mdata['currentsubid'] = $existingsubid;
            }
            $mdata['freeplanavailable'] = $freeplanavailable;
            $mdata['freeplanid'] = $freeplanid;
            $mdata['freeplanbillinginterval'] = $freeplanbillinginterval;
            $monthly = $this->render_from_template('block_poodllclassroom/newplancontainer', $mdata);
        }

        // yearly plans
        $ydata = [];
        $ydata['display'] = '';
        $ydata['billinginterval'] = 'Yearly';
        $ydata['currency'] = 'USD';
        $ydata['billingintervallabel'] = get_string('yearly', constants::M_COMP);
        if($checkoutexisting) {
            $ydata['checkoutexisting'] = $checkoutexisting;
            $ydata['currentsubid'] = $existingsubid;
        }
        if($platform == constants::M_PLATFORM_MOODLE && $planfamily == 'ALL'){
            $langplans = [];
            $mediaplans = [];
            $essentialsplans = [];
            $englishcentralplans = [];
            foreach($yearlyplans as $theplan){
                switch($theplan->planfamily){
                    case constants::M_FAMILY_LANG:
                        $theplan->display = $theplan->upstreamplan == 'Poodll-Languages-Lite' ? '' : 'block_poodllclassroom_hidden';
                        $langplans[] = $theplan;
                        break;
                    case constants::M_FAMILY_MEDIA:
                        $theplan->display = $theplan->upstreamplan == 'Poodll-Media-Lite' ? '' : 'block_poodllclassroom_hidden';
                        $mediaplans[] = $theplan;
                        break;
                    case constants::M_FAMILY_ESSENTIALS:
                        $theplan->display = $theplan->upstreamplan == 'Poodll-Essentials-Lite' ? '' : 'block_poodllclassroom_hidden';
                        $essentialsplans[] = $theplan;
                        break;
                    case constants::M_FAMILY_EC:
                        $theplan->display = '';
                        $englishcentralplans[] = $theplan;
                        break;
                }
            }
            if(count($mediaplans) > 0){$ydata['mediaplans'] = $mediaplans;
            }
            if(count($langplans) > 0){$ydata['langplans'] = $langplans;
            }
            if(count($essentialsplans) > 0){$ydata['essentialsplans'] = $essentialsplans;
            }
            if(count($englishcentralplans) > 0){$ydata['englishcentralplans'] = $englishcentralplans;
            }
            $ydata['freeplanavailable'] = $freeplanavailable;
            $ydata['freeplanid'] = $freeplanid;
            $ydata['freeplanbillinginterval'] = $freeplanbillinginterval;
            $yearly = $this->render_from_template('block_poodllclassroom/moodleplanscontainer', $ydata);
        }else {
            $ydata['plans'] = $yearlyplans;
            if($checkoutexisting) {
                $ydata['checkoutexisting'] = $checkoutexisting;
                $ydata['currentsubid'] = $existingsubid;
            }
            $ydata['freeplanavailable'] = $freeplanavailable;
            $ydata['freeplanid'] = $freeplanid;
            $ydata['freeplanbillinginterval'] = $freeplanbillinginterval;
            $yearly = $this->render_from_template('block_poodllclassroom/newplancontainer', $ydata);
        }

        return $freely .$togglediv . $monthly . $yearly;

    }

    function fetch_changeplan_toppart($school, $platform=constants::M_PLATFORM_MOODLE, $planfamily=constants::M_FAMILY_ALL) {
        global $CFG;

        $tabsdata = [];
        if($school && isset($school->resold)){
            $returnurl = new \moodle_url(constants::M_URL . '/subs/schooldetails.php', ['id' => $school->id , 'type' => 'school', 'resellerid' => $school->resellerid]);
        }else{
            $returnurl = new \moodle_url($CFG->wwwroot . '/my/');
        }
        $tabsdata['returnurl'] = $returnurl->out();
        $checkouturl = new \moodle_url(constants::M_URL . '/subs/changesubscription.php', ['schoolid' => $school->id , 'planfamily' => $planfamily]);
        $tabsdata['checkouturl'] = $checkouturl->out();
        $tabsdata['platform_' . strtoupper($platform)] = 1;
        // $tabsdata['school']=$school;
        // $tabsdata['planfamily']=$planfamily;
        $ret = $this->render_from_template('block_poodllclassroom/changepagetabs', $tabsdata);

        return $ret;

    }

    function fetch_changeplan_buttons($extendedsub) {

        if(!$extendedsub){
            $ret = \html_writer::div(get_string('youhavenosubscription', constants::M_COMP), constants::M_COMP . '_nosubscription');
            return $ret;

        }
        $checkoutexisting = true;
        $school = common::get_school_by_sub($extendedsub);
        // this will limit what sub options are shown
        // $planfamily = $extendedsub->plan->planfamily;

        // this will show all platform sub options
        $planfamily = constants::M_FAMILY_ALL;

        return self::fetch_checkout_buttons($school,
            $extendedsub->plan->platform,
            $planfamily,
            $checkoutexisting,
            $extendedsub->id);

    }



    function create_user_list($users, $tableid, $visible) {
        global $USER;

        $data = [];
        $data['display'] = $visible ? 'block' : 'none';
        $data['tableid'] = $tableid;
        $data['items'] = [];
        // loop through the items,massage data and add to table
        // itemname itemid,filename,itemdate, id
        $currentitem = 0;

        foreach ($users as $user) {
            if($user->lastaccess) {
                $lastaccess = date("Y-m-d H:i:s", $user->lastaccess);
            }else{
                $lastaccess = '--:--';
            }
            $ditem = [];
            $ditem['id'] = $user->id;
            $ditem['firstname'] = $user->firstname;
            $ditem['lastname'] = $user->lastname;
            $ditem['lastaccess'] = $lastaccess;
            if($user->id == $USER->id){
                $ditem['isme'] = true;
            }
            $data['items'][] = $ditem;

        }
        return $this->render_from_template('block_poodllclassroom/userlisttable', $data);

    }

    /**
     * No items, thats too bad
     */
    public function no_users($visible) {
        $data = [];
        $data['display'] = $visible ? 'block' : 'none';
        return $this->render_from_template('block_poodllclassroom/nouserscontainer', $data);
    }

    function create_course_list($courses, $visible) {
        global $CFG;
        $data = [];
        $data['display'] = $visible ? '' : 'block_poodllclassroom_hidden';
        $data['courses'] = [];
        // loop through the items,massage data and add to table
        // itemname itemid,filename,itemdate, id
        $currentitem = 0;
        foreach ($courses as $course) {
            $ditem = [];
            $ditem['id'] = $course->courseid;
            $ditem['coursename'] = $course->coursename;
            $ditem['wwwroot'] = $CFG->wwwroot;
            $data['courses'][] = $ditem;
        }
        return $this->render_from_template('block_poodllclassroom/courselist', $data);

    }

    /**
     * No items, thats too bad
     */
    public function no_courses($visible) {
        $data = [];
        $data['display'] = $visible ? '' : 'block_poodllclassroom_hidden';
        return $this->render_from_template('block_poodllclassroom/nocoursescontainer', $data);
    }

    function setup_datatables($tableid, $usercount=0) {
        global $USER;

        $tableprops = [];
        $columns = [];
        // for cols .. .'itemname', 'itemtype', 'itemtags','timemodified', 'edit','delete'
        $columns[0] = null;
        $columns[1] = null;
        $columns[2] = null;
        $columns[3] = ['orderable' => false];
        $columns[4] = ['orderable' => false];
        $tableprops['columns'] = $columns;
        $tableprops['deferRender'] = true;

        // default ordering
        $order = [];
        $order[0] = [2, "desc"];
        $tableprops['order'] = $order;

        if($usercount < 5){
            $tableprops['searching'] = false;
            $tableprops['paging'] = false;
        }

        // here we set up any info we need to pass into javascript
        $opts = [];
        $opts['tableid'] = $tableid;
        $opts['tableprops'] = $tableprops;
        $this->page->requires->js_call_amd(constants::M_COMP . "/datatables", 'init', [$opts]);
        $this->page->requires->css( new \moodle_url('https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css'));
    }

    /**
     *  Show a single button.
     */
    public function js_trigger_button($buttontag, $visible, $buttonlabel, $bootstrapclass='btn-primary') {

        $buttonclass = constants::M_CLASS  . '_' . $buttontag . '_btn';
        $containerclass = $buttonclass . '_cnt';
        $button = \html_writer::link('#', $buttonlabel, ['class' => 'btn ' . $bootstrapclass . ' ' . $buttonclass, 'type' => 'button', 'id' => $buttonclass, 'data-id' => 0]);
        $visibleclass = '';
        if(!$visible){$visibleclass = 'hide';
        }
        $ret = \html_writer::div($button, $containerclass . ' ' .  $visibleclass);
        return $ret;
    }

    // fetch modal container
    function fetch_modalcontainer($title, $content, $containertag) {
        $data = [];
        $data['title'] = $title;
        $data['content'] = $content;
        $data['containertag'] = $containertag;
        return $this->render_from_template('block_poodllclassroom/modalcontainer', $data);
    }


    // fetch modal content
    function fetch_modalcontent($title, $content) {
        $data = [];
        $data['title'] = $title;
        $data['content'] = $content;
        return $this->render_from_template('block_poodllclassroom/modalcontent', $data);
    }


    function fetch_dosomething_button($blockid, $courseid) {
        // single button is a Moodle helper class that creates simple form with a single button for you
        $triggerbutton = new \single_button(
            new \moodle_url(constants::M_URL . '/view.php', ['blockid' => $blockid, 'courseid' => $courseid, 'dosomething' => 1]),
            get_string('dosomething', constants::M_COMP), 'get');

        return \html_writer::div( $this->render($triggerbutton), constants::M_COMP . '_triggerbutton');
    }
    function fetch_triggeralert_button() {
        // these are attributes for a simple html button.
        $attributes = [];
        $attributes['type'] = 'button';
        $attributes['id'] = \html_writer::random_id(constants::M_COMP . '_');
        $attributes['class'] = constants::M_COMP . '_triggerbutton';
        $button = \html_writer::tag('button', get_string('triggeralert', constants::M_COMP), $attributes);

        // we attach an event to it. The event comes from a JS AMD module also in this plugin
        $opts = ['buttonid' => $attributes['id']];
        $this->page->requires->js_call_amd(constants::M_COMP . "/triggeralert", 'init', [$opts]);

        // we want to make our language strings available to our JS button too
        // strings for JS
        $this->page->requires->strings_for_js([
            'triggeralert_message',
        ],
            constants::M_COMP);

        // finally return our button for display
        return $button;
    }


    // return a button that will allow user to add a new sub
    function fetch_addplan_button() {
        $thebutton = new \single_button(
                new \moodle_url(constants::M_URL . '/subs/edit.php', []),
                get_string('addplan', constants::M_COMP), 'get');
        return $thebutton;
    }

    // Fetch subs table
    function fetch_plans_table($plans) {
        global $DB;

        $params = [];
        $baseurl = new \moodle_url(constants::M_URL . '/subs/subs.php', $params);

        // add sub button
        $addbutton = $this->fetch_addplan_button();

        $billingintervals = common::fetch_billingintervals();
        $data = [];
        foreach($plans as $plan) {
            $fields = [];
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

            $buttons = [];

            $urlparams = ['id' => $plan->id, 'type' => 'plan', 'returnurl' => $baseurl->out_as_local_url()];

            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php', $urlparams),
                    $this->output->pix_icon('t/edit', get_string('edit')),
                    ['title' => get_string('edit')]);

            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php',
                $urlparams + ['duplicate' => 1]),
                $this->output->pix_icon('t/copy', get_string('copy')),
                ['title' => get_string('copy')]);

            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php',
                        $urlparams + ['delete' => 1]),
                        $this->output->pix_icon('t/delete', get_string('delete')),
                        ['title' => get_string('delete')]);

            $fields[] = implode(' ', $buttons);

            $data[] = $row = new \html_table_row($fields);
        }

        $table = new \html_table();
        $table->head  = [get_string('id', constants::M_COMP),
                get_string('planname', constants::M_COMP),
                get_string('billinginterval', constants::M_COMP),
                get_string('maxusers', constants::M_COMP),
                get_string('maxcourses', constants::M_COMP),
                get_string('features', constants::M_COMP),
                get_string('upstreamplan', constants::M_COMP),
                get_string('price', constants::M_COMP),
                get_string('planfamily', constants::M_COMP),
                get_string('description', constants::M_COMP),
                get_string('action')];
        $table->colclasses = ['leftalign name', 'leftalign size', 'centeralign action'];

        $table->id = constants::M_ID_PLANSTABLE;
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = $data;

        // return add button and table
        $heading = $this->output->heading('Plans', 3);
        return  $heading . $this->render($addbutton) .  \html_writer::table($table);

    }

    // return a button that will allow user to add a new sub
    function fetch_addsub_button() {
        $thebutton = new \single_button(
            new \moodle_url(constants::M_URL . '/subs/edit.php', ['type' => 'sub']),
            get_string('addsub', constants::M_COMP), 'get');
        return $thebutton;
    }

    // return a button that will allow user to add a new school
    function fetch_addschool_button() {
        $thebutton = new \single_button(
                new \moodle_url(constants::M_URL . '/subs/edit.php', ['type' => 'school']),
                get_string('addschool', constants::M_COMP), 'get');
        return $thebutton;
    }

    // return a button that will allow user to add a new school
    function fetch_addresellerschool_button() {
        $thebutton = new \single_button(
            new \moodle_url(constants::M_URL . '/subs/editmyschool.php', ['id' => 0, 'add' => 1]),
            get_string('addschool', constants::M_COMP), 'get');
        return $thebutton;
    }

    function fetch_addreseller_button() {
        $thebutton = new \single_button(
                new \moodle_url(constants::M_URL . '/subs/edit.php', ['type' => 'reseller']),
                get_string('addreseller', constants::M_COMP), 'get');
        return $thebutton;
    }


    // Fetch subs table
    function fetch_subs_table($subs) {
        global $DB;

        $params = [];
        $baseurl = new \moodle_url(constants::M_URL . '/subs/subs.php', $params);
        $plans = common::fetch_plans();
        $billingintervals = common::fetch_billingintervals();

        // add sub button
        $context = \context_system::instance();
        if(has_capability('block/poodllclassroom:manageintegration', $context)) {
            $abutton = $this->fetch_addsub_button();
            $addnewbutton = $this->render($abutton);
        }else{
            $addnewbutton = '';
        }

        $data = [];
        foreach($subs as $sub) {
            $fields = [];
            $fields[] = $sub->id;
            $fields[] = $sub->schoolname;
            $fields[] = $plans[$sub->planid]->name  . "($sub->planid) " . $billingintervals[$plans[$sub->planid]->billinginterval];
            $fields[] = $sub->upstreamsubid;
            $fields[] = $sub->status;
            $fields[] = $sub->jsonfields;
            $fields[] = date('Y-m-d', $sub->timemodified);

            $buttons = [];

            $urlparams = ['id' => $sub->id, 'type' => 'sub', 'returnurl' => $baseurl->out_as_local_url()];

            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php', $urlparams),
                    $this->output->pix_icon('t/edit', get_string('edit')),
                    ['title' => get_string('edit')]);

            /* remove delete option for now */
            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php',
                    $urlparams + ['delete' => 1]),
                    $this->output->pix_icon('t/delete', get_string('delete')),
                    ['title' => get_string('delete')]);

            $fields[] = implode(' ', $buttons);

            $data[] = $row = new \html_table_row($fields);
        }

        $table = new \html_table();
        $table->head  = [get_string('id', constants::M_COMP),
                get_string('school', constants::M_COMP),
                get_string('plan', constants::M_COMP),
                get_string('upstreamsubid', constants::M_COMP),
                get_string('status', constants::M_COMP),
                get_string('jsonfields', constants::M_COMP),
                get_string('lastchange', constants::M_COMP),
                get_string('action')];
        $table->colclasses = ['leftalign name', 'leftalign size', 'centeralign action'];

        $table->id = constants::M_ID_SUBSTABLE;
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = $data;

        // return add button and table
        $heading = $this->output->heading('Subs', 3);
        return   $heading  . $addnewbutton .  \html_writer::table($table);

    }

    // Fetch schools table
    function fetch_schools_table($schools, $returnurl) {
        global $DB;

        $superadmin = false;
        $reseller = false;

        // add school button
        $context = \context_system::instance();
        if(has_capability('block/poodllclassroom:manageintegration', $context)) {
            $abutton = $this->fetch_addschool_button();
            $addnewbutton = $this->render($abutton);
            $superadmin = true;
        }else{
            $reseller = common::fetch_me_reseller();
            if($reseller) {
                $abutton = $this->fetch_addresellerschool_button();
                $addnewbutton = $this->render($abutton);
            }
        }

        $data = [];
        foreach($schools as $school) {
            $urlparams = ['id' => $school->id, 'type' => 'school', 'returnurl' => $returnurl->out_as_local_url()];
            $schooldetailsurl = new \moodle_url(constants::M_URL . '/subs/schooldetails.php', $urlparams);

            $fields = [];
            $fields[] = $school->id;
            $fields[] = \html_writer::link($schooldetailsurl,
                    $school->name,
                    ['title' => get_string('view')]);
            if($superadmin) {
                $fields[] = $school->ownerfirstname . ' ' . $school->ownerlastname . "($school->ownerid)";
                $fields[] = $school->owneremail;
                $fields[] = $school->upstreamownerid;
                // $fields[] = $school->status;
                // $fields[] = $school->jsonfields;
            }
            // get next expiry
            if(isset($school->nextexpiry) && !empty($school->nextexpiry)) {
                $fields[] = date("Y-m-d", $school->nextexpiry);// date('Y-m-d', $school->timemodified);
            }else{
                $fields[] = '-';
            }
            $fields[] = date("Y-m-d", $school->timemodified);// date('Y-m-d', $school->timemodified);

            $buttons = [];
            // view school subs and other details
            $buttons[] = \html_writer::link($schooldetailsurl,
                    $this->output->pix_icon('t/preview', get_string('view')),
                    ['title' => get_string('view')]);

            if($superadmin) {
                $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php', $urlparams),
                    $this->output->pix_icon('t/edit', get_string('edit')),
                    ['title' => get_string('edit')]);
            }else if($reseller){
                $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/editmyschool.php', $urlparams),
                    $this->output->pix_icon('t/edit', get_string('edit')),
                    ['title' => get_string('edit')]);
            }

            if($superadmin) {
                $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php',
                    $urlparams + ['delete' => 1]),
                    $this->output->pix_icon('t/delete', get_string('delete')),
                    ['title' => get_string('delete')]);
            }else if($reseller) {
                $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/editmyschool.php',
                    $urlparams + ['delete' => 1]),
                    $this->output->pix_icon('t/delete', get_string('delete')),
                    ['title' => get_string('delete')]);

            }

            $fields[] = implode(' ', $buttons);

            $data[] = $row = new \html_table_row($fields);
        }

        $table = new \html_table();
        $table->head  = [];

        $table->head[] = get_string('id', constants::M_COMP);
        $table->head[] = get_string('school', constants::M_COMP);
        if($superadmin) {
            $table->head[] = get_string('owner', constants::M_COMP);
            $table->head[] = get_string('owneremail', constants::M_COMP);
            $table->head[] = get_string('upstreamownerid', constants::M_COMP);
            // $table->head[] = get_string('status', constants::M_COMP);
            // $table->head[] = get_string('jsonfields', constants::M_COMP);
        }
        $table->head[] = get_string('nextexpiry', constants::M_COMP);
        $table->head[] = get_string('lastchange', constants::M_COMP);
        $table->head[] = get_string('action');
        $table->colclasses = ['leftalign name', 'leftalign size', 'centeralign action'];

        $table->id = constants::M_ID_SCHOOLSTABLE;
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = $data;

        // return add button and table
        $heading = $this->output->heading(get_string('myschools', constants::M_COMP), 3, null, 'myschools');
        return   $heading  . $addnewbutton . \html_writer::table($table);

    }

    // Fetch renewers table
    function fetch_renewers_table($schools, $returnurl) {
        global $DB;

        $config = get_config(constants::M_COMP);

        $data = [];
        foreach($schools as $school) {
            $urlparams = ['id' => $school->id, 'type' => 'school', 'returnurl' => $returnurl->out_as_local_url()];
            $schooldetailsurl = new \moodle_url(constants::M_URL . '/subs/schooldetails.php', $urlparams);

            $fields = [];
            $fields[] = $school->id;
            $fields[] = \html_writer::link($schooldetailsurl,
                $school->name,
                ['title' => get_string('view')]);

            $fields[] = $school->ownerfirstname . ' ' . $school->ownerlastname . "($school->ownerid)";
            $fields[] = $school->owneremail;
            $cburl = "https://" . $config->chargebeesiteprefix . ".chargebee.com/d/customers/" . $school->upstreamownerid;
            $fields[] = \html_writer::link($cburl, $school->upstreamownerid);

            $fields[] = $school->totalrecordings;
            $fields[] = $school->totalmins;
            $fields[] = $school->maxmonthusers;

            // get next expiry
            if(isset($school->nextexpiry) && !empty($school->nextexpiry)) {
                $fields[] = date("Y-m-d", $school->nextexpiry);// date('Y-m-d', $school->timemodified);
            }else{
                $fields[] = '-';
            }
            $fields[] = date("Y-m-d", $school->timemodified);// date('Y-m-d', $school->timemodified);

            $buttons = [];
            // view school subs and other details
            $buttons[] = \html_writer::link($schooldetailsurl,
                $this->output->pix_icon('t/preview', get_string('view')),
                ['title' => get_string('view')]);

            $fields[] = implode(' ', $buttons);

            $data[] = $row = new \html_table_row($fields);
        }

        $table = new \html_table();
        $table->head  = [];

        $table->head[] = get_string('id', constants::M_COMP);
        $table->head[] = get_string('school', constants::M_COMP);
        $table->head[] = get_string('owner', constants::M_COMP);
        $table->head[] = get_string('owneremail', constants::M_COMP);
        $table->head[] = get_string('upstreamownerid', constants::M_COMP);
        $table->head[] = get_string('totalrecordings', constants::M_COMP);
        $table->head[] = get_string('totalmins', constants::M_COMP);
        $table->head[] = get_string('maxmonthusers', constants::M_COMP);
        $table->head[] = get_string('nextexpiry', constants::M_COMP);
        $table->head[] = get_string('lastchange', constants::M_COMP);
        $table->head[] = get_string('action');
        $table->colclasses = ['leftalign name', 'leftalign size', 'centeralign action'];

        $table->id = constants::M_ID_SCHOOLSTABLE;
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = $data;

        // return add button and table
        $heading = $this->output->heading('Upcoming Renewals', 3);
        return   $heading  . \html_writer::table($table);

    }

    // Fetch resellers table
    function fetch_resellers_table($resellers) {
        global $DB;

        $params = [];
        $baseurl = new \moodle_url(constants::M_URL . '/subs/subs.php', $params);

        // add school button
        $addbutton = $this->fetch_addreseller_button();

        $data = [];
        foreach($resellers as $reseller) {
            $fields = [];
            $fields[] = $reseller->id;
            $fields[] = $reseller->name;
            $fields[] = $reseller->resellerfirstname . ' ' . $reseller->resellerlastname . "($reseller->userid)";
            $fields[] = $reseller->upstreamuserid;
            $fields[] = $reseller->jsonfields;
            $fields[] = date('Y-m-d', $reseller->timemodified);

            $buttons = [];

            $urlparams = ['id' => $reseller->id, 'type' => 'reseller', 'returnurl' => $baseurl->out_as_local_url()];

            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/resellerdetails.php', $urlparams),
                $this->output->pix_icon('t/preview', get_string('view')),
                ['title' => get_string('view')]);

            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php', $urlparams),
                    $this->output->pix_icon('t/edit', get_string('edit')),
                    ['title' => get_string('edit')]);

            /* remove delete option for now */
            $buttons[] = \html_writer::link(new \moodle_url(constants::M_URL . '/subs/edit.php',
                    $urlparams + ['delete' => 1]),
                    $this->output->pix_icon('t/delete', get_string('delete')),
                    ['title' => get_string('delete')]);

            $fields[] = implode(' ', $buttons);

            $data[] = $row = new \html_table_row($fields);
        }

        $table = new \html_table();
        $table->head  = [get_string('id', constants::M_COMP),
                get_string('reseller', constants::M_COMP),
                get_string('user', constants::M_COMP),
                get_string('upstreamowner', constants::M_COMP),
                get_string('jsonfields', constants::M_COMP),
                get_string('lastchange', constants::M_COMP),
                get_string('action')];
        $table->colclasses = ['leftalign name', 'leftalign size', 'centeralign action'];

        $table->id = constants::M_ID_RESELLERTABLE;
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = $data;

        // return add button and table
        $heading = $this->output->heading('Resellers', 3);
        return   $heading  . $this->render($addbutton) .  \html_writer::table($table);

    }

    function fetch_other_options() {
        // add school button
        $context = \context_system::instance();
        if(!has_capability('block/poodllclassroom:manageintegration', $context)) {
            return "";
        }

        $schoolsbutton = new \single_button(
            new \moodle_url(constants::M_URL . '/subs/sync.php', ['type' => 'schools']),
            get_string('syncschools', constants::M_COMP), 'get');
        $subsbutton = new \single_button(
            new \moodle_url(constants::M_URL . '/subs/sync.php', ['type' => 'subs']),
            get_string('syncsubs', constants::M_COMP), 'get');
        $eventrunnerbutton = new \single_button(
            new \moodle_url(constants::M_URL . '/subs/eventrunner.php', []),
            get_string('eventrunner', constants::M_COMP), 'get');

        $removebogusschools = new \single_button(
            new \moodle_url(constants::M_URL . '/subs/sync.php', ['type' => 'removebogusschools']),
            get_string('removebogusschools', constants::M_COMP), 'get');

        // return add button and table
        $heading = $this->output->heading(get_string('syncoptions', constants::M_COMP), 3);
        $heading .= \html_writer::div(get_string('syncoptions_instructions', constants::M_COMP));

        return $heading  . $this->render($schoolsbutton) .
            $this->render($subsbutton) .
            $this->render($eventrunnerbutton) .
            $this->render($removebogusschools);

    }



    /*
     * Takes data from webservice about usage and renders it on page
     */

    public function display_usage_report($reportdata, $rawusagedata) {

        $plugintypesarr = [];

        if($rawusagedata->usersubs_details) {
            foreach ($rawusagedata->usersubs_details as $subdatadetails) {
                $jsonarr = json_decode($subdatadetails->file_by_app, true);
                foreach ($jsonarr as $key => $val) {
                    $label = $key;
                    $val = $jsonarr[$key]['audio'] + $jsonarr[$key]['video'];
                    if(isset($jsonarr[$key]['other'])){$val += $jsonarr[$key]['other'];
                    }
                    if (isset($plugintypesarr[$label])) {
                        $plugintypesarr[$label] += $val;
                    } else {
                        $plugintypesarr[$label] = $val;
                    }
                }
            }
        }

        // build html to return
        $ret = $this->output->render_from_template('block_poodllclassroom/usagereport', $reportdata);

        if ($reportdata['subscription_check'] == true){
            $plugintypes = new \core\chart_series('Plugin Usage', array_values($plugintypesarr));
            $pchart = new \core\chart_pie();
            $pchart->add_series($plugintypes);
            $pchart->set_labels(array_keys($plugintypesarr));
            $ret .= $this->output->heading(get_string('per_plugin', constants::M_COMP), 4);
            $ret .= $this->output->render($pchart);
        }

        return $ret;
    }

     /*
      * Count the unique users from CSV list of users. Used by Display usage report
      *
      */
    public function count_pusers($pusers) {
        $pusers = trim($pusers);
        return count(array_unique(explode(',', $pusers)));

    }

    public function fetch_schooldetailsform_instructions($returnurl='') {
        return $this->output->render_from_template('block_poodllclassroom/schooldetailsinstructions', ['returnurl' => $returnurl]);
    }

    /* RS - Added to close layout tag structure */

    public function fetch_schooldetails_close_tags() {
        return "</section></section>";
    }

    public function footer_note($platform) {
        $templatecontext = ['supportlink' => new \moodle_url('https://poodll.com/contact/')];
        $templatecontext['platform_'.constants::M_PLATFORM_MOODLE] = strtoupper($platform) == constants::M_PLATFORM_MOODLE;
        $templatecontext['platform_'.constants::M_PLATFORM_LTI] = strtoupper($platform) == constants::M_PLATFORM_LTI;
        return $this->render_from_template('block_poodllclassroom/footer_note', $templatecontext);
    }

}
