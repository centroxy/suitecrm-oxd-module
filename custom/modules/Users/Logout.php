<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


include ('include/MVC/preDispatch.php');
require_once('include/entryPoint.php');
$db = DBManagerFactory::getInstance();

function select_query($db, $action){
	$result = $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE '$action'"))["gluu_value"];
	return $result;
}
if(isset($_SESSION['session_in_op'])){
	if(time()<(int)$_SESSION['session_in_op']) {
		require_once("modules/Gluussos/oxd-rp/Logout.php");
		$db = DBManagerFactory::getInstance();
		$gluu_provider = select_query($db, 'gluu_provider');
		$json = file_get_contents($gluu_provider . '/.well-known/openid-configuration');
		$obj = json_decode($json);

		$oxd_id = select_query($db, 'gluu_oxd_id');
		$gluu_config = json_decode(select_query($db, 'gluu_config'), true);
		if (!empty($obj->end_session_endpoint)) {
			if (!empty($_SESSION['user_oxd_id_token'])) {
				if ($oxd_id && $_SESSION['user_oxd_id_token'] && $_SESSION['session_in_op']) {
					$logout = new Logout();
					$logout->setRequestOxdId($oxd_id);
					$logout->setRequestIdToken($_SESSION['user_oxd_id_token']);
					$logout->setRequestPostLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
					$logout->setRequestSessionState($_COOKIE['session_state']);
					$logout->setRequestState($_COOKIE['state']);
					$logout->request();
					unset($_SESSION['user_oxd_access_token']);
					unset($_SESSION['user_oxd_id_token']);
					unset($_SESSION['session_state']);
					unset($_SESSION['state']);
					unset($_SESSION['session_in_op']);
					header("Location: " . $logout->getResponseObject()->data->uri);
					exit;
				}
			}
		} else {
			unset($_SESSION['user_oxd_access_token']);
			unset($_SESSION['user_oxd_id_token']);
			unset($_SESSION['session_state']);
			unset($_SESSION['state']);
			unset($_SESSION['session_in_op']);
		}
	}
}
// record the last theme the user used
$current_user->setPreference('lastTheme',$theme);
$GLOBALS['current_user']->call_custom_logic('before_logout');

// submitted by Tim Scott from SugarCRM forums
foreach($_SESSION as $key => $val) {
	$_SESSION[$key] = ''; // cannot just overwrite session data, causes segfaults in some versions of PHP	
}
if(isset($_COOKIE[session_name()])) {
	setcookie(session_name(), '', time()-42000, '/',null,false,true);
}

//Update the tracker_sessions table
// clear out the authenticating flag
session_destroy();

LogicHook::initialize();
$GLOBALS['logic_hook']->call_custom_logic('Users', 'after_logout');

/** @var AuthenticationController $authController */
session_start();
session_destroy();
ob_clean();
$gluu_custom_logout = select_query($db, 'gluu_custom_logout');
if(!empty($gluu_custom_logout)){
	header("Location: $gluu_custom_logout");
}else{
	header('Location: index.php?module=Users&action=Login');
}
sugar_cleanup(true);

