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
global $sugar_config;
$base_url  = $sugar_config['site_url'];
header("Location: $base_url/index.php?module=Users&action=Logout");