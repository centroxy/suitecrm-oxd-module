<?php
/**
 * Created by PhpStorm.
 * User: Vlad-Home
 * Date: 3/18/2016
 * Time: 5:29 PM
 */
// record the last theme the user used


$base_url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
header("Location: $base_url/index.php?module=Users&action=Logout");