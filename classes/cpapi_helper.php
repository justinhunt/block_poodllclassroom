<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 7/31/21
 * Time: 21:37
 */

namespace block_poodllclassroom;

class cpapi_helper {

    public static function make_moodle_user($username, $password,$firstname,$lastname,$email){
        $oneuser=array('username'=>$username,
                'password'=>$password,
                'email'=>$email,
                'firstname'=>$firstname,
                'lastname'=>$lastname);
        $users =array($oneuser);
        $senddata = array('users'=>$users);
        $result =self::curl_wrap('core_user_create_users',$senddata);
        return $result;
    }

    public static  function get_moodle_users($username){
        $criterion=array('key'=>'username','value'=>$username);
        $criteria = array('criteria'=>array($criterion));
        $result =self::curl_wrap('core_user_get_users',$criteria);
        return $result;
    }

    public static  function update_cpapi_user($username, $firstname, $lastname, $email, $expiredate,
            $subscriptionid, $transactionid,$accesskeyid,$accesskeysecret){

        $senddata = array();
        $senddata['username'] =$username;
        $senddata['firstname'] = $firstname;
        $senddata['lastname'] = $lastname;
        $senddata['email'] = $email;
        $senddata['expiredate'] = $expiredate;
        $senddata['subscriptionid'] = $subscriptionid;
        $senddata['transactionid'] = $transactionid;
        $senddata['awsaccessid'] = $accesskeyid;
        $senddata['awsaccesssecret'] = $accesskeysecret;
        $result =self::curl_wrap('local_cpapi_update_cpapi_user',$senddata);

        return $result;
    }

    public static  function update_cpapi_sites($username, $url1,$url2, $url3, $url4, $url5){

        $senddata = array();
        $senddata['username'] =$username;
        $senddata['url1'] = $url1;
        $senddata['url2'] = $url2;
        $senddata['url3'] = $url3;
        $senddata['url4'] = $url4;
        $senddata['url5'] = $url5;
        $result =self::curl_wrap('local_cpapi_update_cpapi_sites',$senddata);

        return $result;
    }

    public static  function reset_cpapi_secret($username,$currentsecret){

        $senddata = array();
        $senddata['username'] =$username;
        $senddata['currentsecret'] =$currentsecret;
        $result =self::curl_wrap('local_cpapi_reset_cpapi_secret',$senddata);

        return $result;
    }


    public static  function curl_wrap($functionname, $data) {

        global $CFG;

        $config = get_config(constants::M_COMP);

        //Just could NOT get POST to work ....WTF
        $method = 'GET';
        $cpapi_url = get_config(constants::M_COMP,'cpapihost') . "/webservice/rest/server.php";
        $cpapi_token = get_config(constants::M_COMP,'cpapitoken');

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
    public static function curl_fetch($url, $postdata = false) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');
        $curl = new \curl();
        // $curl->setopt(array('CURLOPT_ENCODING' => ""));
        $result = $curl->get($url, $postdata);
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