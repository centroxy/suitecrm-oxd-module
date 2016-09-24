<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$dictionary['Gluussos'] = array (
    'table' => 'gluu_table',
    'fields' => array (
        'gluu_action'=>array('name' =>'gluu_action', 'vname'=>'COLUMN_TITLE_NAME', 'type' =>'longtext', 'len'=>'2048'),
        'gluu_value'=>array('name' =>'gluu_value' ,'type' =>'longtext','vname'=>'COLUMN_TITLE_LABEL',  'len'=>'2048')
    )
);
?>
