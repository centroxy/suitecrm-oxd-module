<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
/**
	 * @copyright Copyright (c) 2017, Gluu Inc. (https://gluu.org/)
	 * @license	  MIT   License            : <http://opensource.org/licenses/MIT>
	 *
	 * @package	  OpenID Connect SSO Module by Gluu
	 * @category  Module for SuiteCrm
	 * @version   3.0.1
	 *
	 * @author    Gluu Inc.          : <https://gluu.org>
	 * @link      Oxd site           : <https://oxd.gluu.org>
	 * @link      Documentation      : <https://gluu.org/docs/oxd/3.0.1/plugin/suitecrm/>
	 * @director  Mike Schwartz      : <mike@gluu.org>
	 * @support   Support email      : <support@gluu.org>
	 * @developer Volodya Karapetyan : <https://github.com/karapetyan88> <mr.karapetyan88@gmail.com>
	 *
	 *
	 * This content is released under the MIT License (MIT)
	 *
	 * Copyright (c) 2017, Gluu inc, USA, Austin
	 *
	 * Permission is hereby granted, free of charge, to any person obtaining a copy
	 * of this software and associated documentation files (the "Software"), to deal
	 * in the Software without restriction, including without limitation the rights
	 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the Software is
	 * furnished to do so, subject to the following conditions:
	 *
	 * The above copyright notice and this permission notice shall be included in
	 * all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	 * THE SOFTWARE.
	 *
	 */
include ('include/MVC/preDispatch.php');
$startTime = microtime(true);
require_once('include/entryPoint.php');
ob_start();
require_once('include/MVC/SugarApplication.php');
$app = new SugarApplication();

require_once("modules/Gluussos/oxd-rp/Get_tokens_by_code.php");
require_once("modules/Gluussos/oxd-rp/Get_user_info.php");

$app->startSession();

function getBaseUrl()
{
    $currentPath = $_SERVER['PHP_SELF'];
    $pathInfo = pathinfo($currentPath);
    $hostName = $_SERVER['HTTP_HOST'];
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    if (strpos($pathInfo['dirname'], '\\') !== false) {
        return $protocol . $hostName . "/";
    } else {
        return $protocol . $hostName . $pathInfo['dirname'] . "/";
    }
}
$base_url  = getBaseUrl();
$db = DBManagerFactory::getInstance();
if( isset( $_REQUEST['gluu_login'] ) and strpos( $_REQUEST['gluu_login'], 'Gluussos' ) !== false ) {
    if (isset($_REQUEST['error']) and strpos($_REQUEST['error'], 'session_selection_required') !== false) {
        header("Location: " . login_url('login'));
        exit;
    }

    $oxd_id = select_query($db, 'gluu_oxd_id');
    $gluu_user_role = select_query($db, 'gluu_user_role');
    $http = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? "https://" : "http://";
    $parts = parse_url($http . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
    parse_str($parts['query'], $query);
    $get_tokens_by_code = new Get_tokens_by_code();
    $get_tokens_by_code->setRequestOxdId($oxd_id);
    $get_tokens_by_code->setRequestCode($_REQUEST['code']);
    $get_tokens_by_code->setRequestState($_REQUEST['state']);
    $get_tokens_by_code->request();

    $get_tokens_by_code_array = array();
    if(!empty($get_tokens_by_code->getResponseObject()->data->id_token_claims))
    {
        $get_tokens_by_code_array = $get_tokens_by_code->getResponseObject()->data->id_token_claims;
    }else{
        echo "<script type='application/javascript'>
					alert('Missing claims : Please talk to your organizational system administrator or try again.');
					location.href='".$base_url."';
				 </script>";
        exit;
    }

    $get_user_info = new Get_user_info();
    $get_user_info->setRequestOxdId($oxd_id);
    $get_user_info->setRequestAccessToken($get_tokens_by_code->getResponseAccessToken());
    $get_user_info->request();
    $get_user_info_array = $get_user_info->getResponseObject()->data->claims;
    $_SESSION['session_in_op'] = $get_tokens_by_code->getResponseIdTokenClaims()->exp[0];
    $_SESSION['user_oxd_id_token'] = $get_tokens_by_code->getResponseIdToken();
    $_SESSION['user_oxd_access_token'] = $get_tokens_by_code->getResponseAccessToken();
    $_SESSION['session_state'] = $_REQUEST['session_state'];
    $_SESSION['state'] = $_REQUEST['state'];
    $get_user_info_array = $get_user_info->getResponseObject()->data->claims;
    $reg_first_name = '';
    $reg_user_name = '';
    $reg_last_name = '';
    $reg_email = '';
    $reg_avatar = '';
    $reg_display_name = '';
    $reg_nikname = '';
    $reg_website = '';
    $reg_middle_name = '';
    $reg_country = '';
    $reg_city = '';
    $reg_region = '';
    $reg_gender = '';
    $reg_postal_code = '';
    $reg_fax = '';
    $reg_home_phone_number = '';
    $reg_phone_mobile_number = '';
    $reg_street_address = '';
    $reg_street_address_2 = '';
    $reg_birthdate = '';
    $reg_user_permission = '';
    if (!empty($get_user_info_array->email[0])) {
        $reg_email = $get_user_info_array->email[0];
    } elseif (!empty($get_tokens_by_code_array->email[0])) {
        $reg_email = $get_tokens_by_code_array->email[0];
    }else{
        echo "<script type='application/javascript'>
					alert('Missing claim : (email). Please talk to your organizational system administrator.');
					location.href='".$base_url."';
				 </script>";
        exit;
    }
    if (!empty($get_user_info_array->website[0])) {
        $reg_website = $get_user_info_array->website[0];
    } elseif (!empty($get_tokens_by_code_array->website[0])) {
        $reg_website = $get_tokens_by_code_array->website[0];
    }
    if (!empty($get_user_info_array->nickname[0])) {
        $reg_nikname = $get_user_info_array->nickname[0];
    } elseif (!empty($get_tokens_by_code_array->nickname[0])) {
        $reg_nikname = $get_tokens_by_code_array->nickname[0];
    }
    if (!empty($get_user_info_array->name[0])) {
        $reg_display_name = $get_user_info_array->name[0];
    } elseif (!empty($get_tokens_by_code_array->name[0])) {
        $reg_display_name = $get_tokens_by_code_array->name[0];
    }
    if (!empty($get_user_info_array->given_name[0])) {
        $reg_first_name = $get_user_info_array->given_name[0];
    } elseif (!empty($get_tokens_by_code_array->given_name[0])) {
        $reg_first_name = $get_tokens_by_code_array->given_name[0];
    }
    if (!empty($get_user_info_array->family_name[0])) {
        $reg_last_name = $get_user_info_array->family_name[0];
    } elseif (!empty($get_tokens_by_code_array->family_name[0])) {
        $reg_last_name = $get_tokens_by_code_array->family_name[0];
    }
    if (!empty($get_user_info_array->middle_name[0])) {
        $reg_middle_name = $get_user_info_array->middle_name[0];
    } elseif (!empty($get_tokens_by_code_array->middle_name[0])) {
        $reg_middle_name = $get_tokens_by_code_array->middle_name[0];
    }

    if (!empty($get_user_info_array->country[0])) {
        $reg_country = $get_user_info_array->country[0];
    } elseif (!empty($get_tokens_by_code_array->country[0])) {
        $reg_country = $get_tokens_by_code_array->country[0];
    }
    if (!empty($get_user_info_array->gender[0])) {
        if ($get_user_info_array->gender[0] == 'male') {
            $reg_gender = '1';
        } else {
            $reg_gender = '2';
        }

    } elseif (!empty($get_tokens_by_code_array->gender[0])) {
        if ($get_tokens_by_code_array->gender[0] == 'male') {
            $reg_gender = '1';
        } else {
            $reg_gender = '2';
        }
    }
    if (!empty($get_user_info_array->locality[0])) {
        $reg_city = $get_user_info_array->locality[0];
    } elseif (!empty($get_tokens_by_code_array->locality[0])) {
        $reg_city = $get_tokens_by_code_array->locality[0];
    }
    if (!empty($get_user_info_array->postal_code[0])) {
        $reg_postal_code = $get_user_info_array->postal_code[0];
    } elseif (!empty($get_tokens_by_code_array->postal_code[0])) {
        $reg_postal_code = $get_tokens_by_code_array->postal_code[0];
    }
    if (!empty($get_user_info_array->phone_number[0])) {
        $reg_home_phone_number = $get_user_info_array->phone_number[0];
    } elseif (!empty($get_tokens_by_code_array->phone_number[0])) {
        $reg_home_phone_number = $get_tokens_by_code_array->phone_number[0];
    }
    if (!empty($get_user_info_array->phone_mobile_number[0])) {
        $reg_phone_mobile_number = $get_user_info_array->phone_mobile_number[0];
    } elseif (!empty($get_tokens_by_code_array->phone_mobile_number[0])) {
        $reg_phone_mobile_number = $get_tokens_by_code_array->phone_mobile_number[0];
    }
    if (!empty($get_user_info_array->picture[0])) {
        $reg_avatar = $get_user_info_array->picture[0];
    } elseif (!empty($get_tokens_by_code_array->picture[0])) {
        $reg_avatar = $get_tokens_by_code_array->picture[0];
    }
    if (!empty($get_user_info_array->street_address[0])) {
        $reg_street_address = $get_user_info_array->street_address[0];
    } elseif (!empty($get_tokens_by_code_array->street_address[0])) {
        $reg_street_address = $get_tokens_by_code_array->street_address[0];
    }
    if (!empty($get_user_info_array->street_address[1])) {
        $reg_street_address_2 = $get_user_info_array->street_address[1];
    } elseif (!empty($get_tokens_by_code_array->street_address[1])) {
        $reg_street_address_2 = $get_tokens_by_code_array->street_address[1];
    }
    if (!empty($get_user_info_array->birthdate[0])) {
        $reg_birthdate = $get_user_info_array->birthdate[0];
    } elseif (!empty($get_tokens_by_code_array->birthdate[0])) {
        $reg_birthdate = $get_tokens_by_code_array->birthdate[0];
    }
    if (!empty($get_user_info_array->region[0])) {
        $reg_region = $get_user_info_array->region[0];
    } elseif (!empty($get_tokens_by_code_array->region[0])) {
        $reg_region = $get_tokens_by_code_array->region[0];
    }

    $username = '';
    if (!empty($get_user_info_array->user_name[0])) {
        $username = $get_user_info_array->user_name[0];
    } else {
        $email_split = explode("@", $reg_email);
        $username = $email_split[0];
    }
    if(!empty($get_user_info_array->permission[0])){
        $world = str_replace("[","",$get_user_info_array->permission[0]);
        $reg_user_permission = str_replace("]","",$world);
    }elseif(!empty($get_tokens_by_code_array->permission[0])){
        $world = str_replace("[","",$get_user_info_array->permission[0]);
        $reg_user_permission = str_replace("]","",$world);
    }
	
		$bool = false;
		$gluu_new_roles              = json_decode(select_query($db, 'gluu_new_role'));
		$gluu_users_can_register    = select_query($db, 'gluu_users_can_register');
		if($gluu_users_can_register == 2 and !empty($gluu_new_roles)) {
			foreach ($gluu_new_roles as $gluu_new_role) {
				if (strstr($reg_user_permission, $gluu_new_role)) {
					$bool = true;
				}
			}
			if(!$bool){
				echo "<script>
											alert('You are not authorized for an account on this application. If you think this is an error, please contact your OpenID Connect Provider (OP) admin.');
											window.location.href='" . gluu_sso_doing_logout($get_tokens_by_code->getResponseIdToken(), $_REQUEST['session_state'], $_REQUEST['state']) . "';
										 </script>";
				exit;
			}
		}
		
    $ut = $GLOBALS['current_user']->getPreference('ut');
    include_once('modules/Users/authentication/AuthenticationController.php');
    $login = new AuthenticationController();
    $user = new User();
     if($reg_email){
         $user->retrieve_by_email_address($reg_email);
         if($user->id){
             $user->employee_status = 'Active';
             $user->status = 'Active';
             $user->last_name = $reg_last_name;
             $user->first_name = $reg_first_name;
             $user->phone_home = $reg_home_phone_number;
             $user->phone_mobile = $reg_phone_mobile_number;
             $user->address_street = $reg_street_address;
             $user->address_city = $reg_city;
             $user->address_country = $reg_country;
             $user->address_postalcode = $reg_postal_code;
             $user->external_auth_only = 0;
             $user->save();

             $GLOBALS['current_user'] = $user;
             $_SESSION['authenticated_user_id'] = $user->id;
             $_SESSION['unique_key'] = $sugar_config['unique_key'];
             $GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
             header("Location: index.php?action=index&module=Home");
             exit;
         }
         else{
		         if($gluu_users_can_register == 3){
			         echo "<script>
												alert('You are not authorized for an account on this application. If you think this is an error, please contact your OpenID Connect Provider (OP) admin.');
												window.location.href='" . gluu_sso_doing_logout($get_tokens_by_code->getResponseIdToken(), $_REQUEST['session_state'], $_REQUEST['state']) . "';
											 </script>";
			         exit;
		         }
             $user_hash = User::getPasswordHash($reg_email);
             $user->user_name = $reg_email;
             $user->email1 = $reg_email;
             $user->employee_status = 'Active';
             $user->status = 'Active';
             $user->user_hash = $user_hash;
             $user->last_name = $reg_last_name;
             $user->first_name = $reg_first_name;
             $user->is_admin = $gluu_user_role;
             $user->phone_home = $reg_home_phone_number;
             $user->phone_mobile = $reg_phone_mobile_number;
             $user->address_street = $reg_street_address;
             $user->address_city = $reg_city;
             $user->address_country = $reg_country;
             $user->address_postalcode = $reg_postal_code;
             $user->external_auth_only = 0;
             $user->save();
             $login->login($reg_email, $reg_email, $PARAMS = array());
             header("Location: index.php?action=Login&module=Users");
             exit;
	         }

    }
}


function select_query($db, $action){
    $result = $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE '$action'"))["gluu_value"];
    return $result;
}

function login_url($prompt){
    $db = DBManagerFactory::getInstance();
    $gluu_config           = json_decode(select_query($db, 'gluu_config'),true);
    $gluu_auth_type        = select_query($db, 'gluu_auth_type');
    $gluu_oxd_id        = select_query($db, 'gluu_oxd_id');
    $oxd_id = select_query($db, 'gluu_oxd_id');
    require_once("modules/Gluussos/oxd-rp/Get_authorization_url.php");

    $get_authorization_url = new Get_authorization_url();
    $get_authorization_url->setRequestOxdId($oxd_id);


    $get_authorization_url->setRequestScope($gluu_config['config_scopes']);
    if($gluu_auth_type != "default"){
        $get_authorization_url->setRequestAcrValues([$gluu_auth_type]);
    }else{
        $get_authorization_url->setRequestAcrValues(null);
    }


    $get_authorization_url->setRequestPrompt($prompt);
    $get_authorization_url->request();

    return $get_authorization_url->getResponseAuthorizationUrl();
}
    
/**
 * Doing logout is something is wrong
 */
function gluu_sso_doing_logout($user_oxd_id_token, $session_states, $state)
{
    @session_start();
    require_once("modules/Gluussos/oxd-rp/Logout.php");
    $db = DBManagerFactory::getInstance();
    $gluu_provider = select_query($db, 'gluu_provider');
    $arrContextOptions=array(
      "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
      ),
    );
    $json = file_get_contents($gluu_provider.'/.well-known/openid-configuration', false, stream_context_create($arrContextOptions));
    $obj = json_decode($json);

    $oxd_id = select_query($db, 'gluu_oxd_id');
    $gluu_config = json_decode(select_query($db, 'gluu_config'), true);
    if (!empty($obj->end_session_endpoint ) or $gluu_provider == 'https://accounts.google.com') {
        if (!empty($_SESSION['user_oxd_id_token'])) {
            if ($oxd_id && $_SESSION['user_oxd_id_token'] && $_SESSION['session_in_op']) {
                $logout = new Logout();
                $logout->setRequestOxdId($oxd_id);
                $logout->setRequestIdToken($_SESSION['user_oxd_id_token']);
                $logout->setRequestPostLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                $logout->setRequestSessionState($_SESSION['session_state']);
                $logout->setRequestState($_SESSION['state']);
                $logout->request();
                unset($_SESSION['user_oxd_access_token']);
                unset($_SESSION['user_oxd_id_token']);
                unset($_SESSION['session_state']);
                unset($_SESSION['state']);
                unset($_SESSION['session_in_op']);
                return $logout->getResponseObject()->data->uri;
            }
        }
    }
    
    return getBaseUrl();
}

