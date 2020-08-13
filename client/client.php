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
$domainname = 'http://localhost/moodle';
//$domainname = 'https://cloud.poodll.com';

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
case 'local_cpapi_update_cpapi_user':
    //REGISTER A USER / UPDATE / Add a subscription
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    $params['username'] = 'russell';
    $params['firstname'] = 'Russelliusx';
    $params['lastname'] = 'Crowie';
    $params['email'] = 'russelius@poodll.com';
    $params['expiredate'] = '1526971366';
    $params['subscriptionid'] = '2511';
    $params['transactionid'] = '991972';
    $params['awsaccessid'] = 'AWS123';
    $params['awsaccesssecret'] = 'AWSxxx';
    break;
case  'local_cpapi_reset_cpapi_secret':
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    $params['username'] = 'russell';
    $params['currentsecret'] = 'WRy0PbL7XUB1nj9x';//'Password-123';
    break;
    case 'local_cpapi_fetch_streamingtranscriber':
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    $params['parent'] = 'http://localhost/moodle';
    $params['appid'] = 'filter_poodll';
    $params['owner'] = 'poodll';
    $params['region'] = 'useast1';
    $params['expiretime'] = '300';
    $params['languagecode'] = 'en-US';
    $params['samplerate'] = '16000';
    break;
 case 'local_cpapi_update_cpapi_sites':
            $params = array();
            $params['wstoken'] = $token;
            $params['wsfunction'] = $functionname;
            $params['username'] = 'russell';
            $params['url1'] = 'http://localhost';
            $params['url2'] = 'https://russell.poodll.com';
            $params['url3'] = '';
            $params['url4'] = '';
            $params['url5'] = '';
            break;
case 'local_cpapi_hello_world';
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    break;

case 'local_cpapi_fetch_polly_url';
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    $params['text'] = 'incredulous';
    $params['texttype'] = 'text';
    $params['voice'] = 'Joey';
    $params['owner'] = 'russel';
    $params['appid'] = 'testclient';
    $params['region'] = 'tokyo';
    break;
 case  'local_cpapi_fetch_upload_details':

/// PARAMETERS
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    $params['mediatype'] = 'audio';
   // $params['parent'] = 'https://russell.poodll.com';
     $params['parent'] = 'http://localhost';
    $params['appid'] = 'filter_poodll';
    $params['owner'] = 'poodll';
    $params['region'] = 'useast1';
    $params['expiredays'] = '365';
    $params['transcode'] = '1';
    $params['transcoder'] = 'default';
    $params['transcribe'] = '1';
    $params['subtitle'] = '0';
    $params['transcribelanguage'] = 'en-US';
    $params['transcribevocab'] = 'none';
    $params['notificationurl'] = 'none';
    $params['sourcemimetype'] = 'audio/mp3'; //this can not be empry
    break;

case 'local_cpapi_fetch_convfile_details':

    /// PARAMETERS
    $params = array();
    $params['wstoken'] = $token;
    $params['wsfunction'] = $functionname;
    $params['mediatype'] = 'audio';
    $params['region'] = 'tokyo';
    $params['infilename'] = 'Y_https_elearning.anytimeesl.ca_443__99999_audio_poodllfile5edf01867c10518.mp3';
    $params['outfilename'] = 'poodllfile5edf01867c10518.mp3';
    $params['convfolder'] = 'transcoded/elearning.anytimeesl.ca/';
    break;

case 'local_cpapi_fetch_presignedupload_url';
        $params = array();
        $params['wstoken'] = $token;
        $params['wsfunction'] = $functionname;
        $params['mediatype'] = 'audio';
        $params['minutes'] = 7;
        $params['key'] = 'transcript5.txt';
        $params['iosvideo'] = false;
        $params['region'] = 'tokyo';
        break;


case 'xlocal_cpapi_fetch_presignedupload_url';
        $params = array();
        $params['wstoken'] = $token;
        $params['wsfunction'] = $functionname;
        $params['mediatype'] = 'audio';
        $params['minutes'] = 25;
        $params['key'] = 'somegreataudio.mp3';
        $params['iosvideo'] = false;
        $params['region'] = 'tokyo';
        break;

case 'local_cpapi_does_file_exist';
        $params = array();
        $params['wstoken'] = $token;
        $params['wsfunction'] = $functionname;
        $params['mediatype'] = 'audio';
        $params['inout'] = 'in';
        $params['filename'] = 'Y_https_amideastonline.org_443__99999_audio_poodllfile5edf49a49052f19.mp3'; //'somegreataudio.mp3';
        $params['region'] = 'tokyo';
        break;

case  'local_cpapi_stage_remoteprocess_job':

        /// PARAMETERS
        $params = array();
        $params['wstoken'] = $token;
        $params['wsfunction'] = $functionname;
        $params['host'] = 'localhost';
        $params['mediatype'] = 'audio';
        $params['appid'] = 'filter_poodll';
        $params['owner'] = 'poodll';
        $params['region'] = 'tokyo';
        $params['s3path'] = 'transcoded/elearning.anytimeesl.ca/';
        $params['s3outfilename'] = 'somegreataudio.mp3';
        $params['transcode'] = '1';
        $params['transcoder'] = 'default';
        $params['transcribe'] = '1';
        $params['subtitle'] = '0';
        $params['language'] = 'en-US';
        $params['vocab'] = 'none';
        $params['notificationurl'] = 'none';
        $params['sourcemimetype'] = 'audio/mp3'; //this can not be empry
        break;

 default:
    echo "at least ONE (and only one) API call in client needs to be marked 'true' ...don't you know";
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
