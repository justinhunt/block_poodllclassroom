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

    public static  function update_cpapi_user($username, $firstname, $lastname, $email, $expiredate=0,
            $subscriptionid=0, $transactionid=0,$accesskeyid=0,$accesskeysecret=0){

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

    public static function update_cpapi_sites($username, $url1,$url2, $url3, $url4, $url5){


        //check for blacklisted URL
        $blacklist =['XXXXSITE.edu.vn'];
        foreach($blacklist as $badurl){
            if(!empty($url1) && strpos($url1,$badurl)>0) {
                $url1= '';
            }
            if(!empty($url2) && strpos($url2,$badurl)>0) {
                $url2= '';
            }
            if(!empty($url3) && strpos($url3,$badurl)>0) {
                $url3= '';
            }
            if(!empty($url4) && strpos($url4,$badurl)>0) {
                $url4= '';
            }
            if(!empty($url5) && strpos($url5,$badurl)>0) {
                $url5= '';
            }
        }


        //sanitize username
        $username = strtolower($username);

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

    public static function exists_cpapi_user($username){

        //sanitize username
        $username = strtolower($username);
        $ret = cpapi_helper::get_moodle_users($username);
        $exists =false;
        if($ret && property_exists($ret,'users')){
            if(count($ret->users)>0){
                //$user =$ret->users[0];
                $exists =true;
            }
        }
        return $exists;

    }

    public static function create_random_apiuser(){
        //seeds
        $apiuserseed = "0123456789ABCDEF" . mt_rand(100, 99999);
        return str_shuffle($apiuserseed);
    }

    public static function create_random_apisecret(){
        //seeds
        $apisecretseed = "0123456789ABCDEF" . mt_rand(10, 99);
        $apiextraseed = "-@!#$%&=*+";
        $apilowerseed = "abcdefghijklmnopqrstuvwxyz";

        //$api secret
        return str_shuffle($apisecretseed . substr(str_shuffle($apiextraseed),0,2) . substr(str_shuffle($apilowerseed),0,2));

    }

    /*
* Create a new standard user on cloud poodll com
*/
    public static function create_cpapi_user($firstname,$lastname,$email,$apiusername=''){

        //seeds
        $apiuserseed = "0123456789ghjklm" . mt_rand(100, 99999);
        $apisecretseed = "0123456789ABCDEF" . mt_rand(10, 99);
        $apiextraseed = "-@!#$%&=*+";
        $apilowerseed = "abcdefghijklmnopqrstuvwxyz";

        //$api secret
        $apisecret = str_shuffle($apisecretseed . substr(str_shuffle($apiextraseed),0,2) . substr(str_shuffle($apilowerseed),0,2));

        //use the passed in API username or make a new one
        if(!empty($apiusername)){
            $user_already_exists = self::exists_cpapi_user($apiusername);
        }else{
            $emailbits = explode('@',$email);
            if($emailbits && count($emailbits)>1) {
                $apiusername = $emailbits[0];
            }else {
                $apiusername = $email;
            }
            $apiusername = preg_replace('/[^a-z0-9]+/', '_', strtolower($apiusername ));
            $user_already_exists = self::exists_cpapi_user($apiusername);

        }

        $trycount=0;
        while($user_already_exists && $trycount<15) {
            $apiusername = str_shuffle($apiuserseed);
            $trycount++;
            $user_already_exists = self::exists_cpapi_user($apiusername);
        }

        //if we get a name clash 15 times we are stuck somehow, so cancel
        if($user_already_exists){return false;}

        //sanitize username
        $apiusername = strtolower($apiusername);
        $ret = self::make_moodle_user($apiusername,
            $apisecret,
            $firstname,
            $lastname,
            $email);
        if(is_array($ret)){
            $ret=$ret[0];
        }
        $ret->apiusername=$apiusername;
        $ret->apiuser=$apiusername;
        $ret->apisecret=$apisecret;
        $ret->siteurls=[];
        return $ret;
    }

    public static  function reset_cpapi_secret($username,$currentsecret){

        $senddata = array();
        $senddata['username'] =$username;
        $senddata['currentsecret'] =$currentsecret;
        $result =self::curl_wrap('local_cpapi_reset_cpapi_secret',$senddata);

        return $result;
    }

    public static function fetch_usage_data($username){
        $params=[];
        $params['username']=$username;
        $result = self::curl_wrap('local_cpapi_fetch_user_report',$params);
        if(!$result || !isset($result->returnMessage) || !($usagedata=json_decode($result->returnMessage))){
            return false;
        }else{
            return $usagedata;
        }
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