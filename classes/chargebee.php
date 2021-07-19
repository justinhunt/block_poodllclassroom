<?php


namespace block_poodllclassroom;


class chargebee
{

    public static function get_checkout_new($planid, $currency, $billinginterval, $schoolid=0){
        global $USER, $CFG;
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
            $upstreamuserid=$reseller->upstreamuserid;
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

    public static function get_checkout_existing($planid){
        global $USER, $CFG;
        $sub = common::get_usersub_by_plan($planid);
        $extended_sub = common::get_extended_sub_data([$sub])[0];
        $schoolname=$extended_sub->school->name;
        $customerid = $sub->upstreamownerid;
        $apikey = get_config(constants::M_COMP,'chargebeeapikey');
        $siteprefix = get_config(constants::M_COMP,'chargebeesiteprefix');

        if($customerid && !empty($apikey) && !empty($siteprefix)){
            $url = "https://$siteprefix.chargebee.com/api/v2/hosted_pages/checkout_existing";
            $postdata=[];
            $postdata['redirect_url'] = $CFG->wwwroot . constants::M_URL . '/subs/welcomeback.php';
            $postdata['cancel_url'] = $CFG->wwwroot . '/my';
            $postdata['subscription']= array(
                "id" => $sub->upstreamsubid,
                "plan_id" => $sub->plan->upstreamplan,
                "cf_school_name"=>$schoolname,
            );
            $curlresult = common::curl_fetch($url,$postdata,$apikey);
            $jsonresult = common::make_object_from_json($curlresult);
            if($jsonresult){
                return $jsonresult;
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