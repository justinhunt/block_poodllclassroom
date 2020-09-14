<?php
/**
 * Services definition.
 *
 * @package mod_readaloud
 * @author  Justin Hunt - poodll.com
 */

$functions = array(

    'block_poodllclassroom_get_checkout_existing' => array(
                'classname'   => 'block_poodllclassroom_external',
                'methodname'  => '_get_checkout_existing',
                'description' => '_get_checkout_existing',
                'capabilities'=> 'block/poodllclassroom:managepoodllclassroom',
                'type'        => 'write',
                'ajax'        => true,
    ),

    'block_poodllclassroom_submit_mform' => array(
            'classname'   => 'block_poodllclassroom_external',
            'methodname'  => 'submit_mform',
            'description' => 'submits mform.',
            'capabilities'=> 'block/poodllclassroom:managepoodllclassroom',
            'type'        => 'write',
            'ajax'        => true,
    ),

    'block_poodllclassroom_delete_item' => array(
        'classname'   => 'block_poodllclassroom_external',
        'methodname'  => 'delete_item',
        'description' => 'delete item.',
        'capabilities'=> 'block/poodllclassroom:managepoodllclassroom',
        'type'        => 'write',
        'ajax'        => true,
    ),

    'block_poodllclassroom_create_school' => array(
        'classname'   => 'block_poodllclassroom_external',
        'methodname'  => 'create_sub',
        'description' => 'create sub.',
        'capabilities'=> 'block/poodllclassroom:manageintegration',
        'type'        => 'write',
        'ajax'        => true,
    ),

    'block_poodllclassroom_update_sub' => array(
            'classname'   => 'block_poodllclassroom_external',
            'methodname'  => 'update_sub',
            'description' => 'update sub',
            'capabilities'=> 'block/poodllclassroom:manageintegration',
            'type'        => 'write',
            'ajax'        => true,
    ),

    'block_poodllclassroom_pause_sub' => array(
            'classname'   => 'block_poodllclassroom_external',
            'methodname'  => 'pause_sub',
            'description' => 'pause sub',
            'capabilities'=> 'block/poodllclassroom:manageintegration',
            'type'        => 'write',
            'ajax'        => true,
    ),

    'block_poodllclassroom_resume_sub' => array(
            'classname'   => 'block_poodllclassroom_external',
            'methodname'  => 'resume_sub',
            'description' => 'resume sub',
            'capabilities'=> 'block/poodllclassroom:manageintegration',
            'type'        => 'write',
            'ajax'        => true,
    ),

    'block_poodllclassroom_cancel_sub' => array(
            'classname'   => 'block_poodllclassroom_external',
            'methodname'  => 'cancel_sub',
            'description' => 'cancel sub',
            'capabilities'=> 'block/poodllclassroom:manageintegration',
            'type'        => 'write',
            'ajax'        => true,
    )
);