<?php
/**
 * Services definition.
 *
 * @package mod_readaloud
 * @author  Justin Hunt - poodll.com
 */

$functions = array(

    'block_poodllclassroom_submit_mform' => array(
            'classname'   => 'block_poodllclassroom_external',
            'methodname'  => 'submit_mform',
            'description' => 'submits mform.',
            'capabilities'=> 'mod/poodllclassroom:managepoodllclassroom',
            'type'        => 'write',
            'ajax'        => true,
    ),
);