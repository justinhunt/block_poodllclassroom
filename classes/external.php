<?php


/**
 * External class.
 *
 * @package block_poollclassroom
 * @author  Justin Hunt - Poodll.com
 */

use \block_poodllclassroom\utils;
use \block_poodllclassroom\constants;

class block_poodllclassroom_external extends external_api {


    public static function submit_form_parameters() {
        return new external_function_parameters([
                'cmid' => new external_value(PARAM_INT),
                'subid' => new external_value(PARAM_INT),
                'filename' => new external_value(PARAM_TEXT),
                'itemname' => new external_value(PARAM_TEXT),
                'itemid' => new external_value(PARAM_TEXT),
                'accesskey' => new external_value(PARAM_TEXT)
        ]);
    }

    public static function submit_form($cmid,$subid, $filename,$itemname,$itemid, $accesskey) {
        global $DB, $USER;

        $params = self::validate_parameters(self::submit_form_parameters(),
                array('cmid'=>$cmid,'subid'=>$subid,'filename'=>$filename,'itemname'=>$itemname,'itemid'=>$itemid,'accesskey'=>$accesskey));
        extract($params);

        $cm = get_coursemodule_from_id(constants::M_MODNAME, $cmid, 0, false, MUST_EXIST);
        $themodule = $DB->get_record(constants::M_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
        $modulecontext = \context_module::instance($cm->id);

        //make database items and adhoc tasks
        $ret = new stdClass();
        $ret->success = false;
        $item = utils::save_rec_to_moodle( $themodule, $filename, $subid, $itemname,$itemid,$accesskey);

        if($item){
                $ret->success = true;
                $ret->item = $item;

        }else{
            $ret->message = "Unable to add update database with submission";
        }

        return json_encode($ret);
    }

    public static function submit_form_returns() {
        return new external_value(PARAM_RAW);
    }

}
