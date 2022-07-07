<?php

use \block_poodllclassroom\cpapi_helper;

require_once("../../../config.php");

//if we are exporting html, do that
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('usagereport', 'block_poodllclassroom'), 3);

$rawusagedata = cpapi_helper::fetch_usage_data($USER->username);

if($rawusagedata) {
    $reportdata = \block_poodllclassroom\common::compile_report_data($rawusagedata);
    $renderer = $PAGE->get_renderer('block_poodllclassroom');
    $renderer->display_usage_report($reportdata, $rawusagedata);
}else{
    echo 'no user data';
}

echo $OUTPUT->footer();