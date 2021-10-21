<?php
/**
 * Created by PhpStorm.
 * User: ishineguy
 * Date: 2018/06/16
 * Time: 19:31
 */

namespace block_poodllclassroom;

defined('MOODLE_INTERNAL') || die();

class constants
{
//component name, db tables, things that define app
const M_COMP='block_poodllclassroom';
const M_NAME='poodllclassroom';
const M_URL='/blocks/poodllclassroom';
const M_CLASS='block_poodllclassroom';
const M_HOOKCOUNT =5;
const M_CLASS_USERLIST ='block_poodllclassroom_userlist_cont';

const M_ID_SCHOOLSTABLE = 'schoolstable';
const M_ID_PLANSTABLE ='planstable';
const M_ID_RESELLERTABLE='resellertable';
const M_ID_SUBSTABLE='substable';

const SETTING_NONE ='none';
const SETTING_MICROSOFTAUTH ='microsoftauth';
const SETTING_GOOGLEAUTH ='googleauth';
const SETTING_FACEBOOKAUTH ='facebookauth';
const SETTING_WEBHOOKSFORM='webhooksform';
const SETTING_ENROLKEYFORM ='enrolkeyform';
const SETTING_MANAGEUSERS ='manageusers';
const SETTING_SITEDETAILSFORM='sitedetailsform';
const SETTING_MANAGECOURSES ='managecourses';
const SETTING_ADDCOURSE ='addcourse';

const FORM_ENROLUSER = 'enroluser';
const FORM_CREATEUSER = 'createuser';
const FORM_EDITUSER = 'edituser';
const FORM_DELETEUSER = 'deleteuser';
const FORM_CREATECOURSE = 'createcourse';
const FORM_EDITCOURSE = 'editcourse';
const FORM_DELETECOURSE = 'deletecourse';
const FORM_UPLOADUSER = 'uploaduser';

const M_TABLE_PLANS = 'block_poodllclassroom_plan';
const M_TABLE_SUBS = 'block_poodllclassroom_sub';
const M_TABLE_SCHOOLS = 'block_poodllclassroom_school';
const M_TABLE_RESELLERS = 'block_poodllclassroom_seller';
const M_TABLE_USERS = 'block_poodllclassroom_user';
const M_TABLE_EVENTS = 'block_poodllclassroom_event';

const M_INTEGRATION_POODLLNET = 'poodllnet';
const M_INTEGRATION_CLOUDPOODLL = 'cloudpoodll';

const M_BILLING_YEARLY = 0;
const M_BILLING_MONTHLY = 1;
const M_BILLING_FREE = 2;

const M_PLATFORM_MOODLE = 'MOODLE';
const M_PLATFORM_LTI = 'LTI';
const M_PLATFORM_CLASSROOM = 'CLASSROOM';

const M_RESELLER_THIRDPARTY =0;
const M_RESELLER_POODLL =1;

const M_STATUS_NONE = '-';
const M_STATUS_ACTIVE = 'active';
const M_STATUS_PAYMENTDUE = 'paymentdue'; //not used
const M_STATUS_INACTIVE = 'inactive'; //not used
const M_STATUS_CANCELLED = 'cancelled';
const M_STATUS_PAUSED = 'paused';
const M_STATUS_NONRENEWING = 'non_renewing';
const M_STATUS_FUTURE = 'future';
const M_STATUS_IN_TRIAL = 'in_trial';

const M_FAMILY_ESSENTIALS = 'M_ESSENT';
const M_FAMILY_LANG = 'M_LANG';
const M_FAMILY_MEDIA = 'M_MEDIA';
const M_FAMILY_EC = 'M_EC';
const M_FAMILY_API = 'M_API';
const M_FAMILY_STANDALONE = 'M_STANDALONE';
const M_FAMILY_LEGACY = 'M_LEGACY';
const M_FAMILY_LTI = 'LTI_STANDARD';
const M_FAMILY_CLASSROOM = 'CLASSROOM';
const M_FAMILY_ALL = 'ALL';


}