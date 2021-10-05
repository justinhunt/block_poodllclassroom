<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'block_poodllclassroom', language 'en'
 *
 * @package   block_poodllclassroom
 * @copyright Justin Hunt <poodllsupport@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['blockstring'] = 'Block string';
$string['descconfig'] = 'Description of the config section';
$string['descfoo'] = 'Config description';
$string['headerconfig'] = 'Config section header';
$string['labelfoo'] = 'Config label';
$string['poodllclassroom:addinstance'] = 'Add a Poodll Classroom block';
$string['poodllclassroom:myaddinstance'] = 'Add Poodll Classroom block to my moodle';
$string['poodllclassroom:managesite'] = 'Manage poodll member site';
$string['poodllclassroom:managepoodllclassroom'] = 'Manage poodll classroom';
$string['poodllclassroom:usepoodllclassroom'] = 'Manage poodll classroom';

$string['pluginname'] = 'Poodll Classroom';
$string['welcomeuser'] = 'Welcome {$a->firstname} {$a->lastname}';
$string['something_happened'] = 'Something happened event';
$string['maximumusers'] = 'Max. Users';
$string['maximumusers_desc'] = 'How many users is this Poodll NET site authorised for';
$string['maximumcourses'] = 'Max. Courses';
$string['maximumcourses_desc'] = 'How many courses is this Poodll NET site authorised for';
$string['allowwebhooks'] = 'Allow webhooks';
$string['allowwebhooks_desc'] = 'Allow webhooks';
$string['allowoauth2'] = 'Allow OAUTH2';
$string['allowoauth2_desc'] = 'Allow OAUTH2';
$string['newblock_dosomething_task']=  'Poodll Classroom Admin dosomething task';
$string['gotoviewpage'] = 'Go to the view page';
$string['dosomething'] = 'Do something';
$string['didsomething'] = 'Did something';
$string['triggeralert'] = 'Trigger alert';
$string['triggeralert_message'] = 'You triggered something';
$string['privacy:metadata'] = 'The Poodll Classroom  block does not store any user data.';
$string['siteadmin'] = 'Site Administration';
$string['save'] = 'Save';
$string['gotoadminpage'] = 'Go to the siteadmin page';

$string['messageprovider:maxusersreached'] = 'Maximum user count reached';
$string['maxusersreachedsubject']='Maximum user count reached for: {$a->sitename}';
$string['maxusersreachedbody']='This message is for: {$a->username}. You have reached the maximum user count of {$a->maximumusers} for your site: {$a->sitename}.';
$string['maxusersreachedsmall']='Maximum user count reached for: {$a->sitename}';



$string['microsoftauth_title'] = 'Microsoft OAuth2 Credentials';
$string['microsoftauth_instructions'] = 'To allow students to login with their Microsoft logins, you can can get a clientid and secret from Microsoft and enter them here.';
$string['facebookauth_title'] = 'Facebook OAuth2 Credentials';
$string['facebookauth_instructions'] = 'To allow students to login with their Facebook logins, you can can get a clientid and secret from Facebook and enter them here.';
$string['googleauth_title'] = 'Google OAuth2 Credentials';
$string['googleauth_instructions'] = 'To allow students to login with their Google logins, you can can get a clientid and secret from Google and enter them here.';
$string['webhook_title'] = 'Webhooks';
$string['webhook_instructions'] = 'Poodll Net lets you publish event data from your site to webhooks that you register here. This allows you to use services such as Zapier and IFTT to integrate with other platforms.';
$string['editingsettings'] = 'Editing Settings';
$string['clientid'] = 'Client ID';
$string['clientsecret'] = 'Client Secret';
$string['enrolkey'] = 'Enrolment Key';
$string['enrolkeyform_title'] = 'Enrolment Keys';
$string['enrolkeyform_instructions'] = 'Generate enrolment keys for your courses here.';
$string['sitedetailsform_title'] = 'Site Details';
$string['sitedetailsform_instructions'] = 'Set basic information about your site here.';
$string['manageusers_title'] = 'Manage Users';
$string['manageusers_instructions'] = 'Manage Users';
$string['managecourses_title'] = 'Manage Courses';
$string['managecourses_instructions'] = 'Manage Courses';
$string['addcourse_title'] = 'Add Course';
$string['addcourse_instructions'] = 'Add Course';
$string['failedsetting'] = 'Unable to complete settings update: {$a}';
$string['updatedsetting'] = 'Updated setting: {$a}';


$string['createcourse'] = 'Create Course';
$string['createcourse_mform'] = 'Create Course';
$string['createcoursestart'] = '<i class="fa fa-plus" aria-hidden="true"></i> Course';
$string['editcourse'] = 'Edit Course';
$string['editcourse_mform'] = 'Edit Course';
$string['close'] = 'Close';
$string['enrol'] = 'Enrol';
$string['enroluser'] = 'Enrol User';
$string['enroluser_mform'] = 'Enrol User';

$string['createuser'] = 'Create User';
$string['edituser']="Edit User";
$string['uploaduser']="Upload Users";
$string['createuser_mform'] = 'Create User';
$string['createuserstart'] = '<i class="fa fa-plus" aria-hidden="true"></i> User';
$string['uploaduserstart'] = '<i class="fa fa-plus" aria-hidden="true"></i> Upload';

$string['nousersheader'] = 'No Users';
$string['nousersinfo'] = 'You currently have no users.';
$string['nocoursesheader'] = 'No Courses';
$string['nocoursesinfo'] = 'You currently have no courses.';

$string['deleteuser'] = 'Delete User';
$string['deleteuser_message'] = 'Really delete user: ';
$string['deletecourse'] = 'Delete Course';
$string['deletecourse_message'] = 'Really delete course: ';
$string['deletebuttonlabel'] = 'DELETE';
$string['deletereseller'] = 'Delete Reseller';
$string['deleteresellerconfirm'] = 'Really delete reseller: ';

$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['firstname'] = 'First name';
$string['lastname'] = 'Last name';
$string['date'] = 'Date';
$string['action'] = 'Action';
$string['lastaccess'] = 'Last Access';
$string['currentpicture'] = 'Custom data';

$string['poodllclassroom:managepoodllclassroom']='Manage PoodllClassroom';
$string['poodllclassroom:manageintegration']='Manage PoodllClassroom integration';

$string['sub'] = 'Subscription';
$string['plan'] = 'Plan';
$string['addplan'] = 'Add Plan';
$string['addeditplan'] = 'Add/Edit Plan';
$string['maxusers'] = 'Max Users';
$string['maxcourses'] = 'Max Courses';
$string['editschoolsub'] = 'Edit School Subscription';
$string['maxcourses'] = 'Max Courses';
$string['planname'] = 'Plan Name';
$string['action'] = 'Action';
$string['nopermission'] = 'You do not have adequate permission to do that';
$string['features'] = 'Features';
$string['upstreamplan'] = 'Upstream Plan';
$string['upstreamsubid'] = 'Upstream Sub';
$string['upstreamownerid'] = 'Upstream Owner';
$string['save'] = 'Save';
$string['id'] = 'ID';
$string['subsschoolsplans'] ='Poodll Classroom Subs, Schools and Plans';
$string['name'] = 'Name';
$string['deleteplanconfirm'] = 'Truly delete subscription plan?';
$string['deleteplan'] = 'Delete Subscription Plan';
$string['deletesubconfirm'] = 'Truly delete subscription?';
$string['deletesub'] = 'Delete Subscription';
$string['deleteschoolconfirm'] = 'Truly delete school: {$a}?';
$string['deleteschool'] = 'Delete School';
$string['company'] = 'Company';
$string['owner'] = 'Owner';
$string['chargebeeapikey'] = 'Chargebee API Key';
$string['chargebeeapikey_desc'] = 'Enter the Chargebee API Key which allows you to build self serve access portal links';
$string['chargebeesiteprefix'] = 'Chargebee Site Prefix';
$string['chargebeesiteprefix_desc'] = 'The part before chargebee,com, e.g poodll-test';
$string['sendingtoportal'] = 'Sending you to subscription portal. Hang on...';
$string['couldnotsendtoportal'] = 'Unable to send to subscription portal';
$string['editmysub'] = 'Edit my subscription';
$string['superadminarea'] = 'Super admin area';
$string['integrationtype']='Integration type';
$string['integrationtype_desc']='Which Poodll site type is this integration on?';
$string['status'] = 'Status';
$string['lastchange'] = 'Last Change';
$string['changeplan'] = 'Change Plan';
$string['billinginterval'] = 'Billing Interval';
$string['price'] = 'Price';
$string['description'] = 'Description';
$string['monthly'] = 'Monthly';
$string['yearly'] = 'Yearly';
$string['free'] = 'Free';
$string['users'] = 'Users';
$string['planfamily'] = 'Plan Family';
$string['platform'] = 'Platform';
$string['courses'] = 'Courses';
$string['upgradeplanheader'] = 'Change Plan Options';
$string['monthlyyearly'] = 'Monthly <--> Yearly';
$string['yourcurrentplan'] = '* current plan *';
$string['changeplaninstructions'] = 'Choose a subscription plan from the options below. Annual and monthly payment options can be selected. The prices shown are unadjusted prices. If your plan is an annual one, when selected the new plan\'s price will be adjusted to factor in the cost from now until the end of the original subscription period.' ;
$string['youhavenosubscription'] = 'You do not have a subscription, so we can not change your plan.';
$string['poodllclassroomoptions'] = 'Account Options';
$string['maximumcourses'] = '<i>Maximum {$a} courses</i>';
$string['maximumusers'] = '<i>Maximum {$a} users</i>';
$string['returntotop'] = 'Return to Dashboard';
$string['toomanyusers'] = 'Too many users: ({$a})';
$string['toomanycourses'] = 'Too many courses: ({$a})';

$string['delimiter'] = 'Delimiter Character';
$string['delim_tab'] = 'Tab';
$string['delim_comma'] = 'Comma';
$string['delim_pipe'] = 'Pipe';
$string['importdata'] = 'Import Data';
$string['importresults'] = 'Imported users: {$a->imported} Failed: {$a->failed}';
$string['returnedrows'] = 'Some rows could not be imported. They have been returned. They either already exist or are incorrect. Please fix them and re-submit.';
$string['uploadinstructions'] = 'Enter or paste each user to upload on a new line below: first name, last name, email, password [optional] ';

$string['upstreamsub'] = 'Upstream Sub';
$string['upstreamowner'] = 'Upstream Owner';
$string['addschool'] = 'Add School';
$string['editschool'] = 'Edit School';
$string['jsonfields'] = 'Extra';
$string['addsub'] = 'Add Sub';
$string['schoolname'] = 'School Name';
$string['editmyschool'] = 'Edit School Details';
$string['school'] = 'School';
$string['existingsubsforplan'] = 'You can not delete a plan if subs based on it exist!!';
$string['existingsubsforschool'] = 'You can not delete a school if subs based on it exist!!';
$string['existingschoolsforreseller'] = 'You can not delete a reseller if schools based on it exist!!';
$string['timecreated'] = 'Time Created';
$string['subname'] = 'Subscription';
$string['subperiod'] = 'Period';
$string['cancel'] = 'cancel';
$string['renew'] = 'renew';
$string['renewnow'] = 'Renew';
$string['change'] = 'change';
$string['noaccessportal'] = 'Unable to access portal';
$string['siteurl']='Site URL {$a}';
$string['nositeurls']='No site urls have been registered. Click the \'Edit site details\' button below to register your Moodle site URL(s). Poodll will not work on unregistered sites.';
$string['apiuser']='API User';
$string['apisecret']='API Secret';
$string['moodlesubs']='Moodle Site Details';
$string['ltisubs']='Poodll for Platform(LTI) Subscriptions';
$string['classroomsubs']='Poodll NET Subscriptions';
$string['allschoolsubs']='All Poodll Subscriptions';
$string['reseller']='Reseller';
$string['resellername']='Reseller Name';
$string['resellertype']='Reseller Type';
$string['addreseller']='Add Reseller';
$string['user']='User';
$string['youcantaccessthatschool']='You can not access that school';
$string['schooldetails']='On this page you can review the subscriptions for the school, register site URLs and manage billing. Use the "manage subscription" link to update payment details or cancel the subscription. Use the "account options" dropdown to access your billing history. Add new subscriptions to your account using the "Add new" button.';
$string['managesubscriptions']='Manage Subscriptions';
$string['managesub']='Manage';
$string['dontownthisschool']='You can not manage this school';
$string['donthaveaschool']='You do not yet have a school';
$string['upstreamuserid']='Upstream User ID';
$string['nosubs']='No subscriptions to display';
$string['back']='Back';
$string['addnew']='Add new';

$string['checkout'] = 'Checkout.' ;
$string['chooseplan'] = 'Choose' ;
$string['checkoutinstructions'] = 'Choose a subscription plan from the options below. ' ;
$string['youhavenoschool']='You have no school';
$string['subexpiretime'] = 'Expiry';
$string['subcurrency'] = 'Currency';
$string['subpayment'] = 'Payment';
$string['cantupdatereseller'] = 'Can not update reseller of that id';
$string['cantchangereselleruserifschools'] = 'Can not change user of the reseller account if the reseller has schools. The upstream owner id would lose sync';
$string['oneuseronereseller'] = 'A user can have only one reseller account';
$string['badschool'] = 'That school does not look correct';
$string['upgradesub'] = 'Upgrade';
$string['cancelsub'] = 'Cancel';
$string['resellercoupon'] = 'Reseller Coupon';
$string['resellercoupon_desc'] = 'Reseller Coupon';
$string['billingaccount'] = 'Manage Poodll Account';
$string['billinghistory'] = 'Billing History';
$string['paymentsources'] = 'Payment Methods';
$string['freetrial'] = 'Free Trial';
$string['alreadytaken'] = 'Already Subscribed';
$string['paymentdue'] = 'Payment Due';
$string['active'] = 'Active';
$string['inactive'] = 'Inactive';
$string['status'] = 'Status';
$string['chargebeesync_task'] = 'Chargebee Sync';
$string['ltihost'] = 'LTI host';
$string['ltihost_desc'] = 'The LTI host URL';
$string['ltitoken'] = 'LTI token';
$string['ltitoken_desc'] = 'The LTI web services token';
$string['cpapihost'] = 'CPAPI host';
$string['cpapihost_desc'] = 'The CPAPI host URL';
$string['cpapitoken'] = 'CPAPI token';
$string['cpapitoken_desc'] = 'The CPAPI web services token';
$string['subrenewtime'] = 'Renews';
$string['subrenewexpiretime'] = 'Renews/Expires';
$string['editsitedetails'] = ' - Edit site details -';
$string['essentialsplans'] = 'Poodll Essentials (Poodll Media + Poodll Languages)';
$string['mediaplans'] = 'Poodll Media';
$string['langplans'] = 'Poodll Languages';
$string['editreseller'] = 'Edit Reseller';
$string['poodllplanid'] = 'Poodll Plan ID';
$string['false'] = 'false';
$string['true'] = 'true';
$string['showcheckout'] = 'Show in checkout';
$string['syncoptions'] = 'Sync Options';
$string['syncoptions_instructions'] = 'Use the sync options to fill up Poodll Classroom with one or all subs from Chargebee. Events shouldnt fire here, so its just syncing. To be safe disable events in the block admin settings';
$string['syncsubs'] = 'Sync Subs';
$string['syncschools'] = 'Sync Schools';
$string['syncpage'] = 'Sync Subs and Schools';
$string['syncschoolform'] = 'Sync Single School';
$string['upstreamschoolid'] = 'Upstream School ID';
$string['syncallschools'] = 'Sync All Schools';
$string['syncsubform'] = 'Sync Single Sub';
$string['syncallsubs'] = 'Sync All Subs';


//My subscription page
$string['usagereport'] = 'Usage Report';
$string['subscription'] = 'Subscription';
$string['start'] = 'Start';
$string['end'] = 'Expiration';
$string['thirty_days'] = '30 Days';
$string['ninety_days'] = '90 Days';
$string['oneeighty_days'] = '180 Days';
$string['threehundredsixtyfive_days'] = '365 Days';
$string['poodll_users'] = 'Poodll Users';
$string['recordings'] = 'Recordings';
$string['recording_min'] = 'Recording minutes';
$string['per_plugin'] = 'Per Plugin';
$string['per_recording_type'] = 'Per recording type';
$string['video'] = 'Video';
$string['audio'] = 'Audio';
$string['per_plugin'] = 'Per Plugin (Last Yr)';
$string['ppn_filter_poodll'] = 'Poodll Filter';
$string['ppn_assignsubmission_onlinepoodll'] = 'Poodll Submission';
$string['ppn_assignfeedback_onlinepoodll'] = 'Poodll Feedback';
$string['ppn_qtype_poodllrecording'] = 'Poodll Question';
$string['ppn_data_field_poodll'] = 'Poodll DB Field';
$string['ppn_atto_poodll'] = 'Atto Poodll';
$string['ppn_tinymce_poodll'] = 'TinyMCE Poodll';
$string['ppn_repository_poodll'] = 'Poodll Repository';
$string['ppn_filter_generico'] = 'Generico Filter';
$string['ppn_filter_videoeasy'] = 'VideoEasy Filter';
$string['ppn_atto_generico'] = 'Atto Generico';
$string['ppn_atto_subtitle'] = 'Atto Subtitle';
$string['ppn_atto_snippet'] = 'Atto Snippet';
$string['ppn_portfolio_blogexport'] = 'BlogExport';
$string['ppn_local_trigger'] = 'Trigger';
$string['ppn_mod_readaloud'] = 'ReadAloud';
$string['ppn_mod_englishcentral'] = 'EnglishCentral';
$string['ppn_mod_wordcards'] = 'Wordcards';
$string['ppn_mod_pchat'] = 'PChat';
$string['ppn_mod_voicestudio'] = 'Voice Studio';
$string['ppn_voice_studio'] = 'Voice Studio';
$string['ppn_speak_auto_grade'] = 'Speak (auto-grade)';
$string['ppn_essentials_plus'] = 'Essentials (Plus)';
$string['ppn_p_chat_standard'] = 'P-Chat (standard)';
$string['ppn_word_cards_standard'] = 'Word Cards (Standard)';
$string['ppn_na'] = 'NA';
$string['no_subscriptions'] = 'No subscriptions.';

$string['enablecpapievents'] = 'Enable CPAPI events';
$string['enablecpapievents_desc'] = 'If disabled, then events will not be sent to the Cloud Poodll Server for creating users, or updating sites.';


$string['per_plugin'] = 'Per Plugin';
$string['nousagedata'] = 'No usage data available.<br>';

$string['unabletocreatesub'] = 'Unable to create subscription.';
$string['unabletoverifysub'] = 'Unable to verify the created subscription.';
$string['createdsub'] = 'Successfully created new subscription. Thanks!';
$string['unknowncbstatus'] = 'Received unknown status: {$a}';




