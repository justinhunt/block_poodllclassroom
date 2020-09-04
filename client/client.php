<?php
// This client for local_cpapi is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//

/**
 * XMLRPC client for Moodle 2 - local_cpapi
 *
 * This script does not depend of any Moodle code,
 * and it can be called from a browser.
 *
 * @author Jerome Mouneyrac
 */

/// MOODLE ADMINISTRATION SETUP STEPS
// 1- Install the plugin
// 2- Enable web service advance feature (Admin > Advanced features)
// 3- Enable XMLRPC protocol (Admin > Plugins > Web services > Manage protocols)
// 4- Create a token for a specific user and for the service 'My service' (Admin > Plugins > Web services > Manage tokens)
// 5- Run this script directly from your browser: you should see 'Hello, FIRSTNAME'


//DISABLE DISABLE DIASABLE
//we want to keep this so we can use it, but lets disable it.
//return;

//use these to get a token
//ubuntubox 'http://ubuntubox/iomad/login/token.php?username=webservicedog&password=ubuntubox-K9&service=poodllclassroom'
//misc 'http://misc.poodll.com/iomad/login/token.php?username=webservicedog&password=poodllnet-K9&service=poodllclassroom'


/// SETUP - NEED TO BE CHANGED

/// DOMAIN NAME
//$domainname = 'http://ubuntubox/iomad';
$domainname = 'https://misc.poodll.com/iomad';



//ubuntubox TOKEN
$token = '87de4df535e2aabae308e26f456508f4';


//MISC USER TOKEN
$token = '0b4de3426a2b0c2af7a45dc6f4e0774a';


//FUNCTIONS
//$functionname='local_cpapi_fetch_upload_details';
$functionname='block_poodllclassroom_create_school';

switch($functionname){

case 'block_poodllclassroom_create_school':
    //REGISTER A USER / UPDATE / Add a subscription
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    $params['username'] = 'tinyeddie';
    $params['firstname'] = 'Tiny';
    $params['lastname'] = 'eddie';
    $params['email'] = 'tineddie@poodll.com';
    $params['schoolname'] = 'TonyEddie';
    break;

case  'core_user_create_users':
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    //users[0][firstname] = firstname (actually arrays)
    $params['firstname'] = 'Elmer';
    $params['lastname'] = 'Fudd';
    $params['email'] = 'elmerfudd@poodll.com';
    $params['password'] = 'Password-123';
    break;


case  'enrol_manual_enrol_users':
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    //enrolments[0][roleid]= roleid
    $params['roleid'] = 2;
    $params['userid'] = 2;
    $params['courseid'] = 2;
    $params['suspend'] = false;
    break;

 default:
    echo "at least ONE (and only one) API call in client needs to be marked 'true'";
    return;
}

//Token fetcher
/*
https://www.yourmoodle.com/login/token.php?username=USERNAME&password=PASSWORD&service=SERVICESHORTNAME
http://localhost/moodle/login/token.php?username=russell&password=Password-123&service=cloud_poodll
http://localhost/moodle/local/cpapi/poodlltoken.php?username=russell&password=Password-123&service=cloud_poodll
Response:

{token:4ed876sd87g6d8f7g89fsg6987dfh78d}
*/

///REST Call
$restURL = $domainname . "/webservice/rest/server.php";
//params
$params['moodlewsrestformat']='json';//xml
$query = http_build_query($params);
//put it together
$restURL.= '?' . $query;

require_once('./curl.php');
$curl = new curl;
$resp = $curl->post($restURL);
print_r($resp);
die;

///// XML-RPC CALL
header('Content-Type: text/plain');
$serverurl = $domainname . '/webservice/xmlrpc/server.php'. '?wstoken=' . $token;
require_once('./curl.php');
$curl = new curl;
$post = xmlrpc_encode_request($functionname, array($mediatype,$parent));
$resp = xmlrpc_decode($curl->post($serverurl, $post));
print_r($resp);
