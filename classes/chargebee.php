<?php


namespace block_poodllclassroom;


class chargebee
{

    public static function get_checkout_new($planid, $currency, $billinginterval, $schoolid=0){
        global $USER, $CFG, $DB;
        $plan = common::get_plan($planid);
        switch($billinginterval){
            case constants::M_BILLING_MONTHLY:
                $billing='Monthly';
                break;
            case constants::M_BILLING_YEARLY:
            default:
                $billing='Yearly';
                break;

        }
        if(!$plan){
            return false;
        }
        $reseller = common::fetch_me_reseller();
        $school = common::get_resold_or_my_school($schoolid);
        if($reseller){
            if(!$school){return false;}
            if($reseller->id === $school->resellerid) {
                $upstreamuserid = $reseller->upstreamuserid;
            }else{
                $truereseller = $DB->get_record(constants::M_TABLE_RESELLERS,array('id'=>$school->resellerid));
                if($truereseller) {
                    $upstreamuserid = $truereseller->upstreamuserid;
                }else{
                    return false;
                }
            }
        }elseif ($school){
            $upstreamuserid=$school->upstreamownerid;
        }else{
            //in this case we dont gots no school nor gots us no upstreamuserid
            //create a school and a random upstreamid
            $school=common::get_poodllschool_by_currentuser();
            if(!$school){
                $school = common::create_blank_school();
            }
            if($school){
                $upstreamuserid=$school->upstreamownerid;
            }else{
                return false;
            }
        }

        $schoolname=$school->name;
        $customerid = $upstreamuserid;
        $schoolowner = $DB->get_record('user', array('id'=>$school->ownerid));
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');
        $resellercoupon = get_config(constants::M_COMP,'resellercoupon');

        if($customerid && !empty($apikey) && !empty($siteprefix)){
            //$url = "https://$siteprefix.chargebee.com/api/v2/hosted_pages/checkout_new";
            $url = "https://$siteprefix.chargebee.com/api/v2/hosted_pages/checkout_new_for_items";

            $postdata=[];
            $postdata['redirect_url'] = $CFG->wwwroot . constants::M_URL . '/subs/welcomeback.php';
            $postdata['cancel_url'] = $CFG->wwwroot . '/my';
            $postdata['subscription_items']=[];
            $postdata['subscription_items']['item_price_id']=[];
            $postdata['subscription_items']['quantity']=[];

            //hacky way to make sure free trials all use monthly plans (though they show in yearly)
            if(strpos($plan->upstreamplan,'Trial')>0){$billing='Monthly';}

            $postdata['subscription_items']['item_price_id'][0] = $plan->upstreamplan . '-' .  $currency . '-'  . $billing;
            $postdata['subscription_items']['quantity'][0]=1;
            /*
                        $postdata['subscription_items'][0]= array(
                            "plan_id" =>
                            "cf_school_name"=>$schoolname,
                        );
            */
            $postdata['customer']= array(
                "id" => $upstreamuserid,
                "email" => $schoolowner->email,
                "first_name" => $schoolowner->firstname,
                "last_name" => $schoolowner->lastname,
            );
            if($reseller){
                $postdata['company'] = $reseller->name;
            }else{
                $postdata['company'] = $schoolname;
            }

            //allow offline payment
            $postdata['allow_offline_payment_methods'] = 'true';

            //if is reseller, apply coupon code
            if($reseller) {
                $postdata['coupon_ids'] = [];
                $postdata['coupon_ids'][] = $resellercoupon;
            }

            //customfields
            $postdata['subscription']=[];
            $postdata['subscription']['cf_schoolid']=$school->id;
            $postdata['subscription']['cf_planid']=$plan->id;

            //passthrough
            $passthrough = [];
            $passthrough['schoolid']=$school->id;
            $passthrough['planid']=$plan->id;
            $passthrough['currency']=$currency;
            $passthrough['billing']=$billing;
            $postdata['pass_thru_content'] = json_encode($passthrough);


            $curlresult = common::curl_fetch($url,$postdata,$apikey);
            $jsonresult = common::make_object_from_json($curlresult);
            if($jsonresult){
                return $jsonresult;
            }
        }
        return false;
    }

    public static function retrieve_hosted_page($id){
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        $url = "https://$siteprefix.chargebee.com/api/v2/hosted_pages/";
        $url .= $id;

        $postdata=false;
        $curlresult = common::curl_fetch($url,$postdata,$apikey);
        $jsonresult = common::make_object_from_json($curlresult);
        if($jsonresult){
            return $jsonresult;
        }
        return false;
    }

    public static function retrieve_process_events($trace=false){
        global $DB;

        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        $url = "https://$siteprefix.chargebee.com/api/v2/events/";

        $lastevents=$DB->get_records(constants::M_TABLE_EVENTS, null,'occurredat DESC','occurredat', 0,1);
        if($lastevents){
            $lastevent = array_shift($lastevents);
            $lastoccurredat = $lastevent->occurredat;
        }else{
            //just so we dont get a universe of old test subs
            $lastoccurredat = 1627626055;
        }
        if($trace) {
            $trace->output("cbsync:: looking for new subscriptions since:" . $lastoccurredat);
        }

        $postdata=[];
        $postdata['event_type[in]'] = '["subscription_created"]';
        $postdata['occurred_at[after]'] = ''  . $lastoccurredat;
        //this is a GET request
        $qstring= http_build_query($postdata,"",'&');
        $url=$url.='?' . $qstring;
        $curlresult = common::curl_fetch($url,false,$apikey);


        $eventslist = common::make_object_from_json($curlresult);
        if(!$eventslist || !isset($eventslist->list) ||  count($eventslist->list) ==0){
            if($trace) {
                $trace->output("cbsync:: no new subs");
            }
            return false;
        }
        if($trace) {
            $trace->output("cbsync:: " . count($eventslist->list) . " new subs");
        }
        foreach($eventslist->list as $eventcontainer) {
            $theevent=$eventcontainer->event;
            if ($theevent && isset($theevent->occurred_at)) {
                if($trace) {
                    $trace->output("cbsync:: processing sub event: " . $theevent->id);
                }
                $pevent = new \stdClass();
                $pevent->timecreated = time();
                $pevent->timecreated = time();
                $pevent->occurredat = $theevent->occurred_at;
                $pevent->upstreamid = $theevent->id;
                $pevent->type = $theevent->event_type;
                $pevent->content = json_encode($theevent->content);
                if (strpos($pevent->type, 'subscription_') === 0) {
                    $principal = 'subscription';
                } else {
                    $principal = 'other';
                }
                switch ($principal) {
                    case "subscription":
                        $pevent->typeid = $theevent->content->subscription->id;
                        break;
                    case "other":
                    default:
                        $pevent->typeid = 0;
                }
                $pevent->id = $DB->insert_record(constants::M_TABLE_EVENTS, $pevent);

                switch ($pevent->type) {
                    case 'subscription_created':
                        //dont create a subscription twice, that would be bad ...
                        $poodllsub = common::get_poodllsub_by_upstreamsubid($theevent->content->subscription->id);
                        if ($poodllsub == false) {
                            $subscription = $theevent->content->subscription;
                            $invoice = $theevent->content->invoice;
                            common::create_poodll_sub($subscription,$invoice->currency_code,$invoice->amount_paid);
                        }
                        break;
                    default:
                        //do nothing
                }//end of switch
            }//end of is valid event
        }//end of events list loop
        return false;
    }

    public static function get_checkout_existing($planid, $schoolid, $currentsubid){
        global $USER, $CFG;

        $current_sub = common::fetch_extended_sub($currentsubid);
        $schoolname=$current_sub->school->name;
        $customerid = $current_sub->school->upstreamownerid;
        $plan = common::get_plan($planid);

        switch($plan->billinginterval){
            case constants::M_BILLING_MONTHLY:
                $billing='Monthly';
                break;
            case constants::M_BILLING_YEARLY:
            default:
                $billing='Yearly';
                break;
        }

        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');
        $resellercoupon = get_config(constants::M_COMP,'resellercoupon');//POODLLSTANDARDRESELLER-98765
        $reseller =common::fetch_me_reseller();

        if($customerid && !empty($apikey) && !empty($siteprefix)){
            $url = "https://$siteprefix.chargebee.com/api/v2/hosted_pages/checkout_existing_for_items";
            $postdata=[];

            //general
            //allow offline payment
            $postdata['allow_offline_payment_methods'] = 'true';

            //if is reseller, apply coupon code
            if($reseller) {
                $postdata['coupon_ids'] = [];
                $postdata['coupon_ids'][] = $resellercoupon;
            }

            //passthrough
            $passthrough = [];
            $passthrough['schoolid']=$schoolid;
            $passthrough['planid']=$planid;
            //$passthrough['currency']=$currency;
            //$passthrough['billing']=$billing;
            $postdata['pass_thru_content'] = json_encode($passthrough);
            $postdata['replace_items_list'] = 'true';

            $postdata['redirect_url'] = $CFG->wwwroot . constants::M_URL . '/subs/welcomeback.php';
            $postdata['cancel_url'] = $CFG->wwwroot . '/my';
            $postdata['subscription']=[];
            $postdata['subscription']['id'] = $current_sub->upstreamsubid;

            $postdata['subscription_items']=[];
            $postdata['subscription_items']['plan_id']=[];
            $postdata['subscription_items']['cf_school_name']=[];

            $postdata['pass_thru_content'] = json_encode($passthrough);

            $postdata['subscription_items']['plan_id'][0] = $plan->upstreamplan;
            $postdata['subscription_items']['cf_school_name'][0] = $schoolname;

            $postdata['subscription_items']['item_price_id'][0] = $plan->upstreamplan . '-' .  $current_sub->paymentcurr . '-'  . $billing;
            $postdata['subscription_items']['quantity'][0]=1;

            //custom_fields
            $postdata['subscription']=[];
            $postdata['subscription']['cf_schoolid']==$schoolid;
            $postdata['subscription']['cf_planid']=$plan->id;

            $curlresult = common::curl_fetch($url,$postdata,$apikey);
            $jsonresult = common::make_object_from_json($curlresult);
            if($jsonresult){
                return $jsonresult;
            }
        }
        return false;
    }

    //NB an admin can not currently "manage" another users subscription via the portal. It will fail at get_poodllschool_by_currentuser
    //admins should manage over at chargebee. But they can create subs and plans and schools here on moodle
    public static function create_portal_session($upstreamownerid){
        global $CFG, $USER;

        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        //this should work because a reseller schools will have sae upstreamowner and a regular owner will have only one school
        $school = common::get_poodllschool_by_currentuser();

        if($school && !empty($apikey) && !empty($siteprefix)){

            if($school->upstreamownerid !== $upstreamownerid){
                error_log("upstream owner for school : $school->id  $school->upstreamownerid != $upstreamownerid");
                return false;
            }

            $url = "https://$siteprefix.chargebee.com/api/v2/portal_sessions";
            $postdata=[];
            $postdata['redirect_url'] = $CFG->wwwroot . '/my';
            $postdata['customer']= array("id" => $upstreamownerid);
            $curlresult = common::curl_fetch($url,$postdata,$apikey);
            $jsonresult = common::make_object_from_json($curlresult);
            if($jsonresult){
                if(isset($jsonresult->portal_session)) {
                        return $jsonresult->portal_session;
                }else{
                    //this causes infinite redirect ...
                    // redirect($postdata['redirect_url'],get_string('noaccessportal',constants::M_COMP));
                    return '';
                }
            }
        }
        return false;
    }

    public static function get_portalurl_by_upstreamid($upstreamid){
        global $CFG;

        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        if($upstreamid && !empty($apikey) && !empty($siteprefix)){
            $url = "https://$siteprefix.chargebee.com/api/v2/portal_sessions";
            $postdata=[];
            $postdata['redirect_url'] = $CFG->wwwroot . '/my';
            $postdata['customer']= array("id" => $upstreamid);
            $curlresult = common::curl_fetch($url,$postdata,$apikey);
            $jsonresult = common::make_object_from_json($curlresult);
            if($jsonresult){
                if(isset($jsonresult->portal_session->access_url)) {
                    $portalurl = $jsonresult->portal_session->access_url;
                    if ($portalurl && !empty($portalurl)) {
                        return $portalurl;
                    }
                }else{
                    //this causes infinite redirect ...
                    // redirect($postdata['redirect_url'],get_string('noaccessportal',constants::M_COMP));
                    return '';
                }
            }
        }
        return false;
    }

    public static function sync_sub($poodllsub){
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        $url = "https://$siteprefix.chargebee.com/api/v2/subscriptions/";
        $url .= $poodllsub->upstreamsubid;

        $postdata=false;
        $curlresult = common::curl_fetch($url,$postdata,$apikey);
        $jsonresult = common::make_object_from_json($curlresult);
        if($jsonresult){
            if($jsonresult->subscription){
                common::update_poodll_sub($jsonresult->subscription,$poodllsub);
            }
        }
    }

    public static function fetch_chargebee_user($upstreamuserid){
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        $url = "https://$siteprefix.chargebee.com/api/v2/customers/";
        $url .= $upstreamuserid;

        $postdata=false;
        $curlresult = common::curl_fetch($url,$postdata,$apikey);
        $upstream_user = common::make_object_from_json($curlresult);
        if($upstream_user
            && !(isset($upstream_user->http_status_code) && $upstream_user->http_status_code==404)
            && isset($upstream_user->customer)) {
            return $upstream_user;
        }else{
            return false;
        }
    }

    public static function fetch_allchargebee_userids($offset=false){
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');
        $userids=[];
        $url = "https://$siteprefix.chargebee.com/api/v2/customers/";

        $postdata=[];
        $postdata['limit'] =100;
        if($offset){
            $postdata['offset']= $offset;
        }

        $forceget=true;
        $curlresult = common::curl_fetch($url,$postdata,$apikey, $forceget);
        $upstream_users = common::make_object_from_json($curlresult);
        if($upstream_users
            && !(isset($upstream_users->http_status_code) && $upstream_users->http_status_code==404)
            && isset($upstream_users->list)) {

            foreach($upstream_users->list as $listitem){
                $userids[]=$listitem->customer->id;
            }
            if(isset($upstream_users->next_offset)){
                $userids = array_merge($userids , self::fetch_allchargebee_userids($upstream_users->next_offset));
            }
            return $userids;
        }else{
            return [];
        }
    }

    public static function fetch_allchargebee_subids($offset=false){
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');
        $subids=[];
        $url = "https://$siteprefix.chargebee.com/api/v2/subscriptions/";

        $postdata=[];
        $postdata['limit'] =10;
        if($offset){
            $postdata['offset']= $offset;
        }

        $forceget=true;
        $curlresult = common::curl_fetch($url,$postdata,$apikey, $forceget);
        $upstream_subs = common::make_object_from_json($curlresult);
        if($upstream_subs
            && !(isset($upstream_subs->http_status_code) && $upstream_subs->http_status_code==404)
            && isset($upstream_subs->list)) {

            foreach($upstream_subs->list as $listitem){
                $subids[]=$listitem->subscription->id;
            }
            if(isset($upstream_subs->next_offset)){
                $subids = array_merge($subids , self::fetch_allchargebee_subids($upstream_subs->next_offset));
            }
            return $subids;
        }else{
            return [];
        }
    }

    public static function fetch_chargebee_sub($upstreamsubid){
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        $url = "https://$siteprefix.chargebee.com/api/v2/subscriptions/";
        $url .= $upstreamsubid;

        $postdata=false;
        $curlresult = common::curl_fetch($url,$postdata,$apikey);
        $jsonresult = common::make_object_from_json($curlresult);
        if($jsonresult) {
            return $jsonresult;
        }else{
            return false;
        }
    }
}