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
$string['poodllclassroom:managesite'] = 'Manage poodll net site';
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
$string['deleteschoolconfirm'] = 'Truly delete subscription school?';
$string['deleteschool'] = 'Delete Subscription School';
$string['company'] = 'Company';
$string['owner'] = 'Owner';
$string['chargebeeapikey'] = 'Chargebee API Key';
$string['chargebeeapikey_desc'] = 'Enter the Chargebee API Key which allows you to build self serve access portal links';
$string['chargebeesiteprefix'] = 'Chargebee Site Prefix';
$string['chargebeesiteprefix_desc'] = 'The part before chargebee,com, e.g poodll-test';
$string['sendingtoportal'] = 'Sending you to subscription portal. Hang on...';
$string['couldnotsendtoportal'] = 'Unable to send to subscription portal';
$string['editmysub'] = 'Edit my subscription';
$string['editsubs'] = 'Edit subscriptions';
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
$string['courses'] = 'Courses';
$string['upgradeplanheader'] = 'Change Plan Options';
$string['monthlyyearly'] = 'Monthly <--> Yearly';
$string['yourcurrentplan'] = '* current plan *';
$string['changeplaninstructions'] = 'Choose a subscription plan from the options below. Annual and monthly payment options can be selected. The prices shown are unadjusted prices. If your plan is an annual one, when selected the new plan\'s price will be adjusted to factor in the cost from now until the end of the original subscription period.' ;
$string['youhavenosubscription'] = 'You do not have a subscription, so we can not change your plan.';
$string['poodllclassroomoptions'] = 'Options';
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
$string['schoolname'] = 'School Name';
$string['editmyschool'] = 'Change School Name';

