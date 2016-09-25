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
function getBaseUrl()
{
    // output: /myproject/index.php
    $currentPath = $_SERVER['PHP_SELF'];

    // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
    $pathInfo = pathinfo($currentPath);

    // output: localhost
    $hostName = $_SERVER['HTTP_HOST'];

    // output: http://
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    // return: http://localhost/myproject/
    return $protocol.$hostName.$pathInfo['dirname'];
}
$base_url  = getBaseUrl();
header("Location: $base_url/index.php?module=Users&action=Logout");