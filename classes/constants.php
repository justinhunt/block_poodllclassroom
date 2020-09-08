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

const M_TABLE_SUBS = 'block_poodllclassroom_sub';
const M_TABLE_SCHOOLS = 'block_poodllclassroom_school';

}