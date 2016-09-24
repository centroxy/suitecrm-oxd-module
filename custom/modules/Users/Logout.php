<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.

 * SuiteCRM is an extension to SugarCRM Community Edition developed by Salesagility Ltd.
 * Copyright (C) 2011 - 2014 Salesagility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for  technical reasons, the Appropriate Legal Notices must
 * display the words  "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 ********************************************************************************/

/*********************************************************************************

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
/*echo '<pre>';
var_dump($_SESSION);exit;*/
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
$authController->authController->logout();

