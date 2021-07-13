<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 7/6/21
 * Time: 22:01
 */

namespace block_poodllclassroom;

class servicecall {

    function make_cpapi_user($username, $password,$firstname,$lastname,$email){
        $oneuser=array('username'=>$username,
                'password'=>$password,
                'email'=>$email,
                'firstname'=>$firstname,
                'lastname'=>$lastname);
        $users =array($oneuser);
        $senddata = array('users'=>$users);
        $result = $this->curl_wrap('core_user_create_users',$senddata);
        return json_decode($result);
    }

    function get_cpapi_users($username){
        $criterion=array('key'=>'username','value'=>$username);
        $criteria = array('criteria'=>array($criterion));
        $result = $this->curl_wrap('core_user_get_users',$criteria);
        return json_decode($result);
    }

    function update_cpapi_user($username, $firstname, $lastname, $email, $expiredate,
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
        $result = $this->curl_wrap('local_cpapi_update_cpapi_user',$senddata);

        return json_decode($result);
    }

    function update_cpapi_sites($username, $url1,$url2, $url3, $url4, $url5){

        $senddata = array();
        $senddata['username'] =$username;
        $senddata['url1'] = $url1;
        $senddata['url2'] = $url2;
        $senddata['url3'] = $url3;
        $senddata['url4'] = $url4;
        $senddata['url5'] = $url5;
        $result = $this->curl_wrap('local_cpapi_update_cpapi_sites',$senddata);

        return json_decode($result);
    }

    function reset_cpapi_secret($username,$currentsecret){

        $senddata = array();
        $senddata['username'] =$username;
        $senddata['currentsecret'] =$currentsecret;
        $result = $this->curl_wrap('local_cpapi_reset_cpapi_secret',$senddata);

        return json_decode($result);
    }

    function curl_wrap($functionname, $data) {
        global $CFG;

        //Just could NOT get POST to work ....WTF
        $method = 'GET';
        $cpapi_url = $CFG->CPAPI_HOST . "/webservice/rest/server.php";

        $params=array();
        $params['wstoken']=$CFG->CPAPI_TOKEN;
        $params['wsfunction']=$functionname;
        $params['moodlewsrestformat']='json';//xml

        //put all the params together
        $senddata = array_merge($data,$params);


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
        switch ($method) {
            case "POST":
                $url = $cpapi_url;
                $sendpostdata = http_build_query($senddata);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $sendpostdata);
                break;
            case "GET":
                $query = http_build_query($senddata);
                $url = $cpapi_url . '?' . $query;
                curl_setopt($ch, CURLOPT_URL, $url);
                break;
            case "PUT":
                $url = $cpapi_url;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                $url = $cpapi_url;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                break;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Content-type: application/json;",'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }


}