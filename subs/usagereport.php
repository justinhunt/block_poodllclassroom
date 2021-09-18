<?php

use \block_poodllclassroom\cpapi_helper;

require_once("../../../config.php");

//if we are exporting html, do that
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('usagereport', 'block_poodllclassroom'), 3);

$usagedata = cpapi_helper::fetch_usage_data($USER->username);
if($usagedata) {
    $renderer = $PAGE->get_renderer('block_poodllclassroom');
    $renderer->display_usage_report($usagedata);
}else{
    echo 'no user data';
}

echo $OUTPUT->footer();