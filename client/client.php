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
//localhost 'http://localhost/moodle/login/token.php?username=russell&password=Password-123&service=cloud_poodll'
//cloudpoodll 'https://cloud.poodll.com/local/cpapi/poodlltoken.php?username=localhostuser&password=w3gMcbQC0MDyPhCT&service=cloud_poodll'


/// SETUP - NEED TO BE CHANGED

/// DOMAIN NAME
$domainname = 'http://ubuntubox/iomad';
//$domainname = 'https://misc.poodll.com/iomad';

//LOCALHOST USERTOKEN
$token = '73573cf1d0bad1e512e0a861cc35ddef';

//LOCALHOST ADMIN TOKEN
//$token = '1eebe7406f85ca3ea64ea9c11f4cc1c5';


//CLOUDPOODLL USER TOKEN
//$cloudtoken = '643eba92a1447ac0c6a882c85051461a';


//FUNCTIONS
//$functionname='local_cpapi_fetch_upload_details';
$functionname='local_cpapi_fetch_presignedupload_url';
//$functionname='local_cpapi_does_file_exist';
//$functionname='local_cpapi_stage_remoteprocess_job';
//$functionname='local_cpapi_fetch_convfile_details';




switch($functionname){
case 'block_poodllclassroom_create_school':
    //REGISTER A USER / UPDATE / Add a subscription
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    $params['username'] = 'russell';
    $params['firstname'] = 'Russelliusx';
    $params['lastname'] = 'Crowie';
    $params['email'] = 'russelius@poodll.com';
    $params['schoolname'] = 'Gladiator School';
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
