<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 7/31/21
 * Time: 21:37
 */

namespace block_poodllclassroom;

class lti_helper {


    public static  function update_lti_sub($schoolname, $upstreamuserid, $upstreamsubid,$upstreamplanid,$expiretime){

        $senddata = array();
        $senddata['schoolname'] =$schoolname;
        $senddata['upstreamuser'] = $upstreamuserid;
        $senddata['upstreamsub'] = $upstreamsubid;
        $senddata['upstreamplan'] = $upstreamplanid;
        $senddata['expiretime'] = $expiretime;
        $result =self::curl_wrap('block_poodllnetadmin_update_lti_sub',$senddata);
        $ret = json_decode($result);
        return $ret;
    }

    public static  function curl_wrap($functionname, $data) {

        global $CFG;

        $config = get_config(constants::M_COMP);

        //Just could NOT get POST to work ....WTF
        $method = 'GET';
        $cpapi_url = get_config(constants::M_COMP,'ltihost') . "/webservice/rest/server.php";
        $cpapi_token = get_config(constants::M_COMP,'ltitoken');

        $params=array();
        $params['wstoken']=$cpapi_token;
        $params['wsfunction']=$functionname;
        $params['moodlewsrestformat']='json';//xml

        //put all the params together
        $senddata = array_merge($data,$params);

        $response = self::curl_fetch($cpapi_url, $senddata);
        if (!self::is_json($response)) {
            return false;
        }
        $payloadobject = json_decode($response);


        return $payloadobject;
    }

    //we use curl to fetch transcripts from AWS and Tokens from cloudpoodll
    //this is our helper
    //we use curl to fetch transcripts from AWS and Tokens from cloudpoodll
    //this is our helper
    public static function curl_fetch($url, $postdata = false, $forceget=false) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        if($postdata) {
            $postdatastring = http_build_query($postdata, '', '&');
            if($forceget){
                $result = $curl->get($url, $postdata);
            }else{
                $result = $curl->post($url, $postdatastring);
            }

        }else{
            $result = $curl->get($url);
        }
        return $result;
    }

    //see if this is truly json or some error
    public static function is_json($string) {
        if (!$string) {
            return false;
        }
        if (empty($string)) {
            return false;
        }
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}