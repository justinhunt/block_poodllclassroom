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

    'block_poodllclassroom_delete_item' => array(
        'classname'   => 'block_poodllclassroom_external',
        'methodname'  => 'delete_item',
        'description' => 'delete item.',
        'capabilities'=> 'mod/poodllclassroom:managepoodllclassroom',
        'type'        => 'write',
        'ajax'        => true,
    ),

    'block_poodllclassroom_create_school' => array(
        'classname'   => 'block_poodllclassroom_external',
        'methodname'  => 'create_school',
        'description' => 'create school.',
        'capabilities'=> 'mod/poodllclassroom:manageintegration',
        'type'        => 'write',
        'ajax'        => true,
    ),
);