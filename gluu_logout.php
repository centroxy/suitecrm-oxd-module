<?php
/**
 * Created by PhpStorm.
 * User: Vlad-Home
 * Date: 3/18/2016
 * Time: 5:29 PM
 */
// record the last theme the user used

session_start();
ob_start();
function select_query($db, $action){
    $result = $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE '$action'"))["gluu_value"];
    return $result;
}
include ('include/MVC/preDispatch.php');
require_once('include/entryPoint.php');
$db = DBManagerFactory::getInstance();
unset($_SESSION['user_oxd_access_token']);
unset($_SESSION['user_oxd_id_token']);
unset($_SESSION['session_state']);
unset($_SESSION['state']);
unset($_SESSION['session_in_op']);
foreach($_SESSION as $key => $val) {
    $_SESSION[$key] = '';
}
if(isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/',null,false,true);
}

session_destroy();
ob_clean();
$gluu_custom_logout = select_query($db, 'gluu_custom_logout');
if(!empty($gluu_custom_logout)){
    header("Location: $gluu_custom_logout");
}else{
    header('Location: index.php?module=Users&action=Login');
}
sugar_cleanup(true);