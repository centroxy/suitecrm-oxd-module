<?php
/**
 * Created by PhpStorm.
 * User: Vlad-Home
 * Date: 3/18/2016
 * Time: 5:29 PM
 */
// record the last theme the user used

@session_start();
unset($_SESSION['user_oxd_access_token']);
unset($_SESSION['user_oxd_id_token']);
unset($_SESSION['session_state']);
unset($_SESSION['state']);
unset($_SESSION['session_in_op']);
$base_url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
header("Location: $base_url/index.php?module=Users&action=Logout");