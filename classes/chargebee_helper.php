<?php


namespace block_poodllclassroom;


class chargebee_helper
{

    public static function get_checkout_new($planid, $currency, $billinginterval, $schoolid=0, $startdate=0){
        global $USER, $CFG, $DB;

        $ret = [];
        $ret['success']=true;
        $ret['payload']='';

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
            $ret['success']=false;
            $ret['payload']='No plan of that id could be found:' . $planid;
            return  $ret;
        }

        $reseller = common::fetch_me_reseller();
        $school = common::get_resold_or_my_school($schoolid);
        if($reseller){
            if(!$school){
                    $ret['success']=false;
                    $ret['payload']='Got reseller but could not get school of that id:' . $schoolid;
                    return  $ret;
            }
            if($reseller->id === $school->resellerid) {
                $upstreamuserid = $reseller->upstreamuserid;
            }else{
                $truereseller = $DB->get_record(constants::M_TABLE_RESELLERS,array('id'=>$school->resellerid));
                if($truereseller) {
                    $upstreamuserid = $truereseller->upstreamuserid;
                }else{
                        $ret['success']=false;
                        $ret['payload']='Not a true reseller. ID: ' .$school->resellerid ;
                        return  $ret;
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
                $ret['success']=false;
                $ret['payload']='We could not get a schools and we could not create a school. all over.' ;
                return  $ret;
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
            $postdata['cancel_url'] = $CFG->wwwroot . '/my/';
            $postdata['subscription_items']=[];
            $postdata['subscription_items']['item_price_id']=[];
            $postdata['subscription_items']['quantity']=[];

            //hacky way to make sure free trials all use monthly plans (though they show in yearly)
            if(strpos(strtolower($plan->upstreamplan),'trial')>0){$billing='Monthly';}

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
            $postdata['subscription']['cf_schoolid']=$school->name;

            //passthrough
            $passthrough = [];
            $passthrough['schoolid']=$school->id;
            $passthrough['planid']=$plan->id;
            $passthrough['currency']=$currency;
            $passthrough['billing']=$billing;
            $postdata['pass_thru_content'] = json_encode($passthrough);


            $curlresult = common::curl_fetch($url,$postdata,$apikey);
            $jsonresult = common::make_object_from_json($curlresult);
            if($jsonresult && isset($jsonresult->hosted_page)){
                $ret['success']=true;
                $ret['payload']=$jsonresult ;
                return  $ret;
            }else{
                $ret['success']=false;
                $ret['payload']=$curlresult;
                return  $ret;
            }
        }
        $ret['success']=false;
        $ret['payload']='Customer ID, SitePrefix or API Key wrong';
        return  $ret;
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

    public static function retrieve_process_recent_events($trace=false){
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
        $postdata['event_type[in]'] = '["subscription_created","subscription_changed","subscription_renewed",' .
            '"subscription_cancelled","subscription_reactivated","subscription_deleted","customer_changed"]';
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
            $trace->output("cbsync:: " . count($eventslist->list) . " new or changed subs");
        }
        foreach($eventslist->list as $eventcontainer) {
            $theevent=$eventcontainer->event;
            self::process_one_event($theevent, $trace);

        }//end of events list loop
        return false;
    }

    public static function retrieve_process_one_event($eventid, $trace=false){
        global $DB;

        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        $url = "https://$siteprefix.chargebee.com/api/v2/events/";
        $url .= $eventid;

        //this is a GET request
        $forceget=true;
        $curlresult = common::curl_fetch($url,false,$apikey,$forceget);

        $event = common::make_object_from_json($curlresult);
        if(!$event){
            $message = "single event:: failed to retrieve event or invalid";
            if($trace) {
                $trace->output($message);
                return false;
            }else{
                return $message;
            }
        }

        if(!isset($event->event->event_type)){
            $message = "single event:: event is invalid";
            if($trace) {
                $trace->output($message);
                return false;
            }else{
                return $message;
            }
        }

        switch ($event->event->event_type){
            case 'subscription_created':
            case 'subscription_reactivated':
            case 'subscription_renewed':
            case 'subscription_cancelled':
            case 'subscription_changed':
            case 'subscription_deleted':
                break;
            default:
                $message = "single event:: event type cannot be processed: " . $event->event->event_type;
                if($trace) {
                    $trace->output($message);
                    return false;
                }else{
                    return $message;
                }
        }

        $theevent=$event->event;
        self::process_one_event($theevent, $trace);

        return 'that possibly worked';
    }

    public static function process_one_event($theevent, $trace=false){
        global $DB;
        $responded = false;

        if ($theevent && isset($theevent->occurred_at)) {
            if($trace) {
                $trace->output("cbsync:: processing a sub event: " . $theevent->id);
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

            //we do not want to add old events on the events table again, because we use that to know which are the most recent events
            //so if the event exists we are being asked to re- run it. Lets just do that
            $event_already_processed = $DB->get_record(constants::M_TABLE_EVENTS,array('upstreamid'=>$theevent->id));
            if($event_already_processed ){
                $pevent->id = $event_already_processed->id;
            }else{
                $pevent->id = $DB->insert_record(constants::M_TABLE_EVENTS, $pevent);
            }

            switch ($pevent->type) {
                case 'subscription_created':
                case 'subscription_renewed':

                    if($trace) {
                        $trace->output("cbsync:: processing ". $pevent->type . " event: " . $theevent->id);
                    }

                    //Temporarily disable events from
                    /*
                    $resellers =['1692','464','2050','483','782','695','380','2802','1243'];
                 if(in_array($theevent->content->subscription->customer_id, $resellers )){
                     $trace->output("cbsync:: ignoring reseller: "  . $theevent->content->subscription->customer_id);
                     break;
                 }
                    */

                    //create a sub
                    $poodllsub = common::get_poodllsub_by_upstreamsubid($theevent->content->subscription->id);
                    if ($poodllsub == false) {
                        //lets create the school. IF it already exists, nothing bad will happen
                        $ret = common::create_school_from_upstreamid($theevent->content->subscription->customer_id);
                        if($trace && $ret){
                            $trace->output("cbsync:: create school from upstreamid: " . $ret['message']);
                        }

                        $subscription = $theevent->content->subscription;
                        $currency_code = $subscription->currency_code;
                        $amount_paid = $subscription->subscription_items[0]->amount;

                        $subid = common::create_poodll_sub($subscription,$currency_code,$amount_paid,$theevent->content->subscription->customer_id );
                        if($trace){
                            if($subid) {
                                $trace->output("cbsync:: create sub: " . $subid);
                            }else{
                                $trace->output("cbsync:: failed to create sub");
                            }
                        }

                    //renew a sub
                    }else{
                        $upstreamsub = $theevent->content->subscription;
                        $updatedsub = common::update_poodllsub_from_upstream($poodllsub,$upstreamsub);
                        if($updatedsub) {
                            $responded = common::respond_to_updated_upstream_sub($updatedsub, $upstreamsub);
                        }
                        if($trace){
                            if($updatedsub && $responded) {
                                $trace->output("cbsync:: renewed sub: " . $updatedsub->id);
                            }else{
                                $trace->output("cbsync:: failed to renew sub");
                            }
                        }
                    }
                    break;
                case 'subscription_cancelled':

                    if($trace) {
                        $trace->output("cbsync:: processing sub cancelled event: " . $theevent->id);
                    }

                    //dont create a subscription twice, that would be bad ...
                    $poodllsub = common::get_poodllsub_by_upstreamsubid($theevent->content->subscription->id);
                    if ($poodllsub == false) {
                        if($trace) {
                            $trace->output("cbsync:: No sub of that ID found locally. Nothing to cancel: " . $theevent->id);
                        }
                    }else{


                        $upstreamsub = $theevent->content->subscription;

                        //we set the expire date to today if the sub has not been paid
                        if((isset($upstreamsub->cancel_reason))){
                            $upstreamsub->current_term_end=time();
                            $trace->output("cbsync:: appears to be a failure to pay, set expiry to today: " . $upstreamsub->cancel_reason);
                        }

                        $updatedsub = common::update_poodllsub_from_upstream($poodllsub,$upstreamsub);
                        if($updatedsub) {
                            $responded = common::respond_to_updated_upstream_sub($updatedsub, $upstreamsub);
                        }
                        if($trace){
                            if($updatedsub && $responded) {
                                $trace->output("cbsync:: cancelled sub: " . $updatedsub->id);
                            }else{
                                $trace->output("cbsync:: failed to cancel sub");
                            }
                        }
                    }
                    break;

                case 'subscription_reactivated':

                    if($trace) {
                        $trace->output("cbsync:: reactivating sub cevent: " . $theevent->id);
                    }

                    //dont create a subscription twice, that would be bad ...
                    $poodllsub = common::get_poodllsub_by_upstreamsubid($theevent->content->subscription->id);
                    if ($poodllsub == false) {
                        if($trace) {
                            $trace->output("cbsync:: No sub of that ID found locally. Nothing to re-activate: " . $theevent->id);
                        }
                    }else{


                        $upstreamsub = $theevent->content->subscription;
                        $updatedsub = common::update_poodllsub_from_upstream($poodllsub,$upstreamsub);
                        if($updatedsub) {
                            $responded = common::respond_to_updated_upstream_sub($updatedsub, $upstreamsub);
                        }
                        if($trace){
                            if($updatedsub && $responded) {
                                $trace->output("cbsync:: reactivated sub: " . $updatedsub->id);
                            }else{
                                $trace->output("cbsync:: failed to reactivate sub");
                            }
                        }
                    }
                    break;

                case 'subscription_changed':
                    if($trace) {
                        $trace->output("cbsync:: processing sub changed event: " . $theevent->id);
                    }

                    //only change an existing subscription
                    $poodllsub = common::get_poodllsub_by_upstreamsubid($theevent->content->subscription->id);
                    if ($poodllsub != false) {
                        if($trace){
                            $trace->output("cbsync:: updating upstreamsub: " . $theevent->content->subscription->id);
                        }

                        $upstreamsub = $theevent->content->subscription;
                        $updatedsub = common::update_poodllsub_from_upstream($poodllsub,$upstreamsub);
                        if($updatedsub) {
                            $responded = common::respond_to_updated_upstream_sub($updatedsub, $upstreamsub);
                        }
                        if($trace){
                            if($updatedsub && $responded) {
                                $trace->output("cbsync:: updated poodll sub: " . $updatedsub->id);
                            }else{
                                $trace->output("cbsync:: failed to update poodll sub: " . $poodllsub->id);
                            }
                        }
                    }else{
                        if($trace){
                            $trace->output("cbsync:: no pre-existing poodll sub matching upstream sub: " . $theevent->content->subscription->id);
                        }
                    }
                    break;
                case 'subscription_deleted':
                    if($trace) {
                        $trace->output("cbsync:: processing sub deleted event: " . $theevent->id);
                    }

                    //only change an existing subscription twice
                    $poodllsub = common::get_poodllsub_by_upstreamsubid($theevent->content->subscription->id);
                    if ($poodllsub != false) {
                        if($trace){
                            $trace->output("cbsync:: deleting upstreamsub: " . $theevent->content->subscription->id);
                        }

                        $ret = $DB->delete_records(constants::M_TABLE_SUBS,array('id'=>$poodllsub->id));

                        if($trace){
                            if($ret) {
                                $trace->output("cbsync:: deleted poodll sub: " . $poodllsub->id);
                            }else{
                                $trace->output("cbsync:: failed to delete poodll sub: " . $poodllsub->id);
                            }
                        }
                    }else{
                        if($trace){
                            $trace->output("cbsync:: no pre-existing poodll sub matching upstream sub: " . $theevent->content->subscription->id);
                        }
                    }
                    break;

                case 'customer_changed':
                    if($trace) {
                        $trace->output("cbsync:: processing customer changed event: " . $theevent->id);
                    }
                    $customer = $theevent->content->customer;
                    $poodllschools = common::get_schools_by_upstreamownerid($customer->id);
                    if ($poodllschools != false) {
                        if($trace){
                            $trace->output("cbsync:: changing customer details locally: " . $customer->id);
                        }
                        foreach($poodllschools as $poodllschool){
                            if($poodllschool->resellerid == common::fetch_poodll_resellerid()){ //constants::M_RESELLER_POODLL){
                                $poodlluser = $DB->get_record('user',array('id'=>$poodllschool->ownerid));
                                $updateuser=false;
                                $updateschool=false;
                                //user name
                                if($customer->first_name != $poodlluser->firstname){
                                    $updateuser=true;
                                    $poodlluser->firstname=$customer->first_name;
                                }
                                //last name
                                if($customer->last_name != $poodlluser->lastname){
                                    $updateuser=true;
                                    $poodlluser->lastname=$customer->last_name;
                                }
                                //email
                                if($customer->email != $poodlluser->email){
                                    $updateuser=true;
                                    $poodlluser->email=$customer->email;
                                }

                                //update user if user info was changed
                                if($updateuser){
                                    $trace->output("cbsync:: changing customer updating poodll user");
                                    $DB->update_record("user",$poodlluser);
                                    $trace->output("cbsync:: changing customer updating cpapi user");
                                    cpapi_helper::update_cpapi_user($poodllschool->apiuser,$poodlluser->firstname,$poodlluser->lastname,$poodlluser->email);
                                }

                                //Update School if company name altered upstream
                                if($customer->company != $poodllschool->name){
                                    $updateschool=true;
                                    $poodllschool->name=$customer->company;
                                }
                                if($updateschool){
                                    $trace->output("cbsync:: changing customer updating poodllschool");
                                    $DB->update_record(constants::M_TABLE_SCHOOLS,$poodllschool);
                                }
                            }else{
                                $trace->output("cbsync:: changing customer to change is a reseller. not touching school");
                            }
                        }

                    }else{
                        if($trace){
                            $trace->output("cbsync:: no poodll customer with that upstream id: " . $customer->id);
                        }
                    }
                    break;
                default:
                    //do nothing
            }//end of switch
        }//end of is valid event
    }

    //update the subscription custom field with the school id
    public static function update_chargebee_subscription_schoolname( $schoolname, $subs)
    {
        global $USER, $CFG;

        if(empty($schoolname)){return false;}
        if(!$subs || count($subs)==0){return false;}

        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');
        foreach ($subs as $sub){
            $url = "https://$siteprefix.chargebee.com/api/v2/subscriptions/" . $sub->upstreamsubid . '/update_for_items';
            $postdata=[];
            $postdata['cf_schoolid'] = $schoolname;
            $curlresult = common::curl_fetch($url,$postdata,$apikey);
            $jsonresult = common::make_object_from_json($curlresult);
        }
        return true;

    }

    public static function update_chargebee_company($customerid, $companyname){
        global $USER, $CFG;

        if(empty($companyname)){return false;}

        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        if($customerid && !empty($apikey) && !empty($siteprefix)){
            $url = "https://$siteprefix.chargebee.com/api/v2/customers/" . $customerid;
            $postdata=[];
            $postdata['company'] = $companyname;


            $curlresult = common::curl_fetch($url,$postdata,$apikey);
            $jsonresult = common::make_object_from_json($curlresult);
            if($jsonresult){
                return $jsonresult;
            }
        }
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
            case constants::M_BILLING_FREE:
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
            $postdata['cancel_url'] = $CFG->wwwroot . '/my/';
            $postdata['subscription']=[];
            $postdata['subscription']['id'] = $current_sub->upstreamsubid;
            $postdata['subscription']['cf_schoolid']=$schoolname;

            $postdata['subscription_items']=[];
            $postdata['subscription_items']['plan_id']=[];
            $postdata['subscription_items']['plan_id'][0] = $plan->upstreamplan;

            $postdata['subscription_items']['item_price_id'][0] = $plan->upstreamplan . '-' .  $current_sub->paymentcurr . '-'  . $billing;
            $postdata['subscription_items']['quantity'][0]=1;

            //custom_fields
          //  $postdata['subscription']=[];
           // $postdata['subscription']['cf_schoolid']==$schoolid;
           // $postdata['subscription']['cf_planid']=$plan->id;

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
            $postdata['redirect_url'] = $CFG->wwwroot . '/my/';
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
            $postdata['redirect_url'] = $CFG->wwwroot . '/my/';
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

    public static function fetch_scheduled_chargebee_sub($upstreamsubid){
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        $url = "https://$siteprefix.chargebee.com/api/v2/subscriptions/";
        $url .= $upstreamsubid;
        $url .= '/retrieve_with_scheduled_changes';

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