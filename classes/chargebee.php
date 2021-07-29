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
                "email" => $USER->email,
                "first_name" => $USER->firstname,
                "last_name" => $USER->lastname,
            );
            if($reseller){
                $postdata['company'] = $reseller->name;
            }else{
                $postdata['company'] = $schoolname;
            }

            //allow offline payment
            $postdata['allow_offline_payment_methods'] = true;

            //if is reseller, apply coupon code
            if($reseller) {
                $postdata['coupon_ids'] = [];
                $postdata['coupon_ids'][] = $resellercoupon;
            }

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

    public static function get_checkout_existing($planid, $schoolid){
        global $USER, $CFG;
        $subs = common::get_usersub_by_plan($planid, $schoolid);
        $extended_subs = common::get_extended_sub_data($subs);
        $extended_sub = array_shift($extended_subs);
        $schoolname=$extended_sub->school->name;
        $customerid = $extended_sub->school->upstreamownerid;
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');
        $resellercoupon = get_config(constants::M_COMP,'resellercoupon');//POODLLSTANDARDRESELLER-98765
        $reseller =common::fetch_me_reseller();

        if($customerid && !empty($apikey) && !empty($siteprefix)){
            $url = "https://$siteprefix.chargebee.com/api/v2/hosted_pages/checkout_existing_for_items";
            $postdata=[];

            //general
            //allow offline payment
            $postdata['allow_offline_payment_methods'] = true;

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


            $postdata['redirect_url'] = $CFG->wwwroot . constants::M_URL . '/subs/welcomeback.php';
            $postdata['cancel_url'] = $CFG->wwwroot . '/my';
            $postdata['subscription']=[];
            $postdata['subscription']['id'] = $extended_sub->upstreamsubid;

            $postdata['subscription_items']=[];
            $postdata['subscription_items']['plan_id']=[];
            $postdata['subscription_items']['cf_school_name']=[];

            $postdata['subscription_items']['plan_id'][0] = $extended_sub->plan->upstreamplan;
            $postdata['subscription_items']['cf_school_name'][0] = $schoolname;

            $curlresult = common::curl_fetch($url,$postdata,$apikey);
            $jsonresult = common::make_object_from_json($curlresult);
            if($jsonresult){
                return $jsonresult;
            }
        }
        return false;
    }

    public static function create_portal_session($upstreamownerid){
        global $CFG, $USER;

        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');
        //this should work because a reseller schools will have sae upstreamowner and a regular owner will have only one school
        $school = common::get_poodllschool_by_currentuser();

        if($school && !empty($apikey) && !empty($siteprefix)){

            if($school->upstreamownerid !== $upstreamownerid){return false;}

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


}