<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
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
 * SugarCRM" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by SugarCRM".
 ********************************************************************************/

/*********************************************************************************

 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
/** @var AuthenticationController $authController */
/*if(isset($_REQUEST['state'])){
	var_dump($_REQUEST);exit;
}*/
$base_url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
require_once("modules/Gluussos/oxd-rp/Get_authorization_url.php");
$db = DBManagerFactory::getInstance();

if(isset($_REQUEST['app_name'])){
	$config_option = json_decode($db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'oxd_config'"))["gluu_value"],true);
	$oxd_id = $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'oxd_id'"))["gluu_value"];
	$get_authorization_url = new Get_authorization_url();
	$get_authorization_url->setRequestOxdId($oxd_id);
	$get_authorization_url->setRequestAcrValues([$_REQUEST['app_name']]);
	$get_authorization_url->request();

	if($get_authorization_url->getResponseAuthorizationUrl()){
		global  $sugar_config;
		$sugar_config['http_referer']['list'][] = 'php-test.gluu.org';
		header( "Location: ". $get_authorization_url->getResponseAuthorizationUrl() );
		exit;
	}else{
		echo '<p style="color: red">Sorry, but oxd server is not switched on!</p>';
	}

}
function login_html(){
	$db = DBManagerFactory::getInstance();
	$oxd_id = $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'oxd_id'"))["gluu_value"];
$html = '';
	if($oxd_id){
		$html.='<style>
			.gluuox_login_icon_preview{
				width:35px;
				cursor:pointer;
				display:inline;
			}
			.customer-account-login .page-title{
				margin-top: -100px !important;
			}
		</style>
		<link  href="modules/Gluussos/GluuOxd_Openid/css/bootstrap-social.css" rel="stylesheet">
		<link  href="modules/Gluussos/GluuOxd_Openid/css/font-awesome.min.css" rel="stylesheet">';


		$get_scopes =   json_decode($db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'scopes'"))["gluu_value"],true);
		$oxd_config =   json_decode($db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'oxd_config'"))["gluu_value"],true);
		$custom_scripts = json_decode($db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'custom_scripts'"))["gluu_value"],true);
		$iconSpace =                  $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'iconSpace'"))["gluu_value"];
		$iconCustomSize =             $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'iconCustomSize'"))["gluu_value"];
		$iconCustomWidth =            $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'iconCustomWidth'"))["gluu_value"];
		$iconCustomHeight =           $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'iconCustomHeight'"))["gluu_value"];
		$loginCustomTheme =           $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'loginCustomTheme'"))["gluu_value"];
		$loginTheme =                 $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'loginTheme'"))["gluu_value"];
		$iconCustomColor =            $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'iconCustomColor'"))["gluu_value"];


		$enableds = array();
		foreach($custom_scripts as $custom_script){
			$enableds[] = array('enable'=>$db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE '".$custom_script['value']."Enable'"), 'value'=>$custom_script['value'], 'name'=>$custom_script['name'], 'image'=>$custom_script['image']);
		}
		if($loginTheme!='longbutton') {

			$html.='<style>.gluuOx_custom_login_icon_preview{cursor:pointer;}</style>';
			if($loginTheme=='circle'){
				$html.='<style> .gluuox_login_icon_preview, .gluuOx_custom_login_icon_preview{border-radius: 999px !important;}</style>';
			 } else if($loginTheme=='oval'){
				$html.='<style> .gluuox_login_icon_preview, .gluuOx_custom_login_icon_preview{border-radius: 5px !important;}</style>';
			 }

			if($loginCustomTheme!='custom') {
				$html.='<div><p style="font-size: 40px">Login with</p>';

					foreach($enableds as $enabled){
						if($enabled['enable']){
							$cl = "socialLogin('".$enabled['value']."')";
							$html.='<img class="gluuox_login_icon_preview" id="gluuox_login_icon_preview_'.$enabled['value'].'" src="'.$enabled['image'].'"
								 style="margin-left: '.$iconSpace.'px;  height:'.$iconCustomSize.'px; width:'.$iconCustomSize.'px;" onclick="'.$cl.'"  />';

						 }
					}
			$html.='</div>
				<br/>';
			 }
		}
$base_url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
$html.="<br>
		<script>


			function socialLogin(appName){
				window.location.href ='$base_url/index.php?module=Users&action=Login&app_name='+appName;
			}
		</script>";
	}
	return $html;
}
$authController->authController->pre_login();

global $current_language, $mod_strings, $app_strings;
if(isset($_REQUEST['login_language'])){
    $lang = $_REQUEST['login_language'];
    $_REQUEST['ck_login_language_20'] = $lang;
	$current_language = $lang;
    $_SESSION['authenticated_user_language'] = $lang;
    $mod_strings = return_module_language($lang, "Users");
    $app_strings = return_application_language($lang);
}
$sugar_smarty = new Sugar_Smarty();
echo '<link rel="stylesheet" type="text/css" media="all" href="'.getJSPath('modules/Users/login.css').'">';
echo '<script type="text/javascript" src="'.getJSPath('modules/Users/login.js').'"></script>';
global $app_language, $sugar_config;
//we don't want the parent module's string file, but rather the string file specifc to this subpanel
global $current_language;

// Get the login page image
if ( sugar_is_file('custom/include/images/sugar_md.png') ) {
    $login_image = '<IMG src="custom/include/images/sugar_md.png" alt="Sugar" width="340" height="25">';
}
else {
    $login_image = '<IMG src="include/images/sugar_md_open.png" alt="Sugar" width="340" height="25" style="margin: 5px 0;">';
}
$gluu_login = login_html();

$sugar_smarty->assign('LOGIN_IMAGE',$login_image);
$sugar_smarty->assign('gluu_login',$gluu_login);

// See if any messages were passed along to display to the user.
if(isset($_COOKIE['loginErrorMessage'])) {
    if ( !isset($_REQUEST['loginErrorMessage']) ) {
        $_REQUEST['loginErrorMessage'] = $_COOKIE['loginErrorMessage'];
    }
    SugarApplication::setCookie('loginErrorMessage', '', time()-42000, '/');
}
if(isset($_REQUEST['loginErrorMessage'])) {
    if (isset($mod_strings[$_REQUEST['loginErrorMessage']])) {
        echo "<p align='center' class='error' > ". $mod_strings[$_REQUEST['loginErrorMessage']]. "</p>";
    } else if (isset($app_strings[$_REQUEST['loginErrorMessage']])) {
        echo "<p align='center' class='error' > ". $app_strings[$_REQUEST['loginErrorMessage']]. "</p>";
    }
}

$lvars = $GLOBALS['app']->getLoginVars();
$sugar_smarty->assign("LOGIN_VARS", $lvars);
foreach($lvars as $k => $v) {
    $sugar_smarty->assign(strtoupper($k), $v);
}

// Retrieve username from the session if possible.
if(isset($_SESSION["login_user_name"])) {
	if (isset($_REQUEST['default_user_name']))
		$login_user_name = $_REQUEST['default_user_name'];
	else
		$login_user_name = $_SESSION['login_user_name'];
} else {
	if(isset($_REQUEST['default_user_name'])) {
		$login_user_name = $_REQUEST['default_user_name'];
	} elseif(isset($_REQUEST['ck_login_id_20'])) {
		$login_user_name = get_user_name($_REQUEST['ck_login_id_20']);
	} else {
		$login_user_name = $sugar_config['default_user_name'];
	}
	$_SESSION['login_user_name'] = $login_user_name;
}
$sugar_smarty->assign('LOGIN_USER_NAME', $login_user_name);

$mod_strings['VLD_ERROR'] = $GLOBALS['app_strings']["\x4c\x4f\x47\x49\x4e\x5f\x4c\x4f\x47\x4f\x5f\x45\x52\x52\x4f\x52"];

// Retrieve password from the session if possible.
if(isset($_SESSION["login_password"])) {
	$login_password = $_SESSION['login_password'];
} else {
	$login_password = $sugar_config['default_password'];
	$_SESSION['login_password'] = $login_password;
}
$sugar_smarty->assign('LOGIN_PASSWORD', $login_password);

if(isset($_SESSION["login_error"])) {
	$sugar_smarty->assign('LOGIN_ERROR', $_SESSION['login_error']);
}
if(isset($_SESSION["waiting_error"])) {
    $sugar_smarty->assign('WAITING_ERROR', $_SESSION['waiting_error']);
}

if (isset($_REQUEST['ck_login_language_20'])) {
	$display_language = $_REQUEST['ck_login_language_20'];
} else {
	$display_language = $sugar_config['default_language'];
}

if (empty($GLOBALS['sugar_config']['passwordsetting']['forgotpasswordON']))
	$sugar_smarty->assign('DISPLAY_FORGOT_PASSWORD_FEATURE','none');

$the_languages = get_languages();
if ( count($the_languages) > 1 )
    $sugar_smarty->assign('SELECT_LANGUAGE', get_select_options_with_id($the_languages, $display_language));
$the_themes = SugarThemeRegistry::availableThemes();
if ( !empty($logindisplay) )
	$sugar_smarty->assign('LOGIN_DISPLAY', $logindisplay);;

// RECAPTCHA

	$admin = new Administration();
	$admin->retrieveSettings('captcha');
	$captcha_privatekey = "";
	$captcha_publickey="";
	$captcha_js = "";
	$Captcha='';

	// if the admin set the captcha stuff, assign javascript and div
	if(isset($admin->settings['captcha_on'])&& $admin->settings['captcha_on']=='1' && !empty($admin->settings['captcha_private_key']) && !empty($admin->settings['captcha_public_key'])){

			$captcha_privatekey = $admin->settings['captcha_private_key'];
			$captcha_publickey = $admin->settings['captcha_public_key'];
			$captcha_js .="<script type='text/javascript' src='" . getJSPath('cache/include/javascript/sugar_grp1_yui.js') . "'></script><script type='text/javascript' src='" . getJSPath('cache/include/javascript/sugar_grp_yui2.js') . "'></script>
			<script type='text/javascript' src='http://api.recaptcha.net/js/recaptcha_ajax.js'></script>
			<script>
			function initCaptcha(){
			Recaptcha.create('$captcha_publickey' ,'captchaImage',{theme:'custom'});
			}
			window.onload=initCaptcha;

			var handleFailure=handleSuccess;
			var handleSuccess = function(o){
				if(o.responseText!==undefined && o.responseText =='Success'){
					generatepwd();
					Recaptcha.reload();
				}
				else{
					if(o.responseText!='')
						document.getElementById('generate_success').innerHTML =o.responseText;
					Recaptcha.reload();
				}
			}
			var callback2 ={ success:handleSuccess, failure: handleFailure };

			function validateAndSubmit(){
					var form = document.getElementById('form');
					var url = '&to_pdf=1&module=Home&action=index&entryPoint=Changenewpassword&recaptcha_challenge_field='+Recaptcha.get_challenge()+'&recaptcha_response_field='+ Recaptcha.get_response();
					YAHOO.util.Connect.asyncRequest('POST','index.php',callback2,url);
			}</script>";
		$Captcha.="<tr>
			<td scope='row' width='20%'>".$mod_strings['LBL_RECAPTCHA_INSTRUCTION'].":</td>
		    <td width='70%'><input type='text' size='26' id='recaptcha_response_field' value=''></td>

		</tr>
		<tr>

		 	<td colspan='2'><div style='margin-left:2px'class='x-sqs-list' id='recaptcha_image'></div></td>
		</tr>
		<tr>
			<td colspan='2' align='right'><a href='javascript:Recaptcha.reload()'>".$mod_strings['LBL_RECAPTCHA_NEW_CAPTCHA']."</a>&nbsp;&nbsp;
			 		<a class='recaptcha_only_if_image' href='javascript:Recaptcha.switch_type(\"audio\")'>".$mod_strings['LBL_RECAPTCHA_SOUND']."</a>
			 		<a class='recaptcha_only_if_audio' href='javascript:Recaptcha.switch_type(\"image\")'> ".$mod_strings['LBL_RECAPTCHA_IMAGE']."</a>
		 	</td>
		</tr>";
		$sugar_smarty->assign('CAPTCHA', $Captcha);
		echo $captcha_js;

	}else{
		echo "<script>
		function validateAndSubmit(){generatepwd();}
		</script>";
	}

$sugar_smarty->display('custom/modules/Users/login.tpl'); ?>
