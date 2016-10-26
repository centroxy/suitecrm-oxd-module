<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("modules/Gluussos/oxd-rp/Register_site.php");
require_once("modules/Gluussos/oxd-rp/Update_site_registration.php");
ob_start();

require_once('include/MVC/SugarApplication.php');
$app = new SugarApplication();
$app->startSession();
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
$db = DBManagerFactory::getInstance();
function select_query($db, $action){
    $result = $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE '$action'"))["gluu_value"];
    return $result;
}
function insert_query($db, $action, $value){
    $result = $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('$action','$value')");
    return $result;
}
function update_query($db, $action, $value){
    $db->query("UPDATE `gluu_table` SET `gluu_value` = '$value' WHERE `gluu_action` LIKE '$action';");
    $result = $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE '$action'"))["gluu_value"];
    return $result;
}

if( isset( $_REQUEST['submit'] ) and strpos( $_REQUEST['submit'], 'delete' )  !== false and !empty($_REQUEST['submit'])) {
    $db->query("DROP TABLE IF EXISTS `gluu_table`;");
    unset($_SESSION['openid_error']);
    $_SESSION['message_success'] = 'Configurations deleted Successfully.';
    SugarApplication::redirect('index.php?module=Gluussos&action=general');
    exit;
}

function remove_http($url) {
    $disallowed = array('http://', 'https://');
    foreach($disallowed as $d) {
        if(strpos($url, $d) === 0) {
            return str_replace($d, '', $url);
        }
    }
    return $url;
}
if( isset( $_REQUEST['form_key'] ) and strpos( $_REQUEST['form_key'], 'general_register_page' ) !== false ) {

    if(!isset($_SERVER['HTTPS']) or $_SERVER['HTTPS'] != "on") {
        $_SESSION['message_error'] = 'OpenID Connect requires https. This plugin will not work if your website uses http only.';
        SugarApplication::redirect('index.php?module=Gluussos&action=general');
        return;
    }
    if($_POST['gluu_user_role']){
        update_query($db, 'gluu_user_role', $_POST['gluu_user_role']);
    }else{
        update_query($db, 'gluu_user_role', 0);
    }
    if ($_POST['gluu_users_can_register']==1) {
        update_query($db, 'gluu_users_can_register', $_POST['gluu_users_can_register']);
        if(!empty(array_values(array_filter($_POST['gluu_new_role'])))){
            update_query($db, 'gluu_new_role', json_encode(array_values(array_filter($_POST['gluu_new_role']))));
            $config = json_decode(select_query($db, 'gluu_config'),true);
            array_push($config['config_scopes'],'permission');
            $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
        }else{
            update_query($db, 'gluu_new_role', json_encode(null));
        }
    }
    elseif($_POST['gluu_users_can_register']==2){
        update_query($db, 'gluu_users_can_register', 2);

        if(!empty(array_values(array_filter($_POST['gluu_new_role'])))){
            update_query($db, 'gluu_new_role', json_encode(array_values(array_filter($_POST['gluu_new_role']))));
            $config = json_decode(select_query($db, 'gluu_config'),true);
            array_push($config['config_scopes'],'permission');
            $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
        }else{
            update_query($db, 'gluu_new_role', json_encode(null));
        }
    }
    if (empty($_POST['gluu_oxd_port'])) {
        $_SESSION['message_error'] = 'All the fields are required. Please enter valid entries.';
        SugarApplication::redirect('index.php?module=Gluussos&action=general');
        return;
    }
    else if (intval($_POST['gluu_oxd_port']) > 65535 && intval($_POST['gluu_oxd_port']) < 0) {
        $_SESSION['message_error'] = 'Enter your oxd host port (Min. number 1, Max. number 65535)';
        SugarApplication::redirect('index.php?module=Gluussos&action=general');
        return;
    }
    else if  (!empty($_POST['gluu_provider'])) {
        if (filter_var($_POST['gluu_provider'], FILTER_VALIDATE_URL) === false) {
            $_SESSION['message_error'] = 'Please enter valid OpenID Provider URI.';
            SugarApplication::redirect('index.php?module=Gluussos&action=general');
            return;
        }
    }
    if  (!empty($_POST['gluu_custom_logout'])) {
        if (filter_var($_POST['gluu_custom_logout'], FILTER_VALIDATE_URL) === false) {
            $_SESSION['message_error'] = 'Please enter valid Custom URI.';
        }else{
            update_query($db, 'gluu_custom_logout', $_POST['gluu_custom_logout']);
        }
    }else{
        update_query($db, 'gluu_custom_logout', '');
    }
    if (isset($_POST['gluu_provider']) and !empty($_POST['gluu_provider'])) {
        $gluu_provider = $_POST['gluu_provider'];
        $gluu_provider = update_query($db, 'gluu_provider', $gluu_provider);
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        $json = file_get_contents($gluu_provider.'/.well-known/openid-configuration', false, stream_context_create($arrContextOptions));
        $obj = json_decode($json);
        if(!empty($obj->userinfo_endpoint)){

            if(empty($obj->registration_endpoint)){
                $_SESSION['message_success'] = "Please enter your client_id and client_secret.";
                $gluu_config = json_encode(array(
                    "gluu_oxd_port" =>$_POST['gluu_oxd_port'],
                    "admin_email" => $GLOBALS['current_user']->email1,
                    "authorization_redirect_uri" => $base_url.'/gluu.php?gluu_login=Gluussos',
                    "post_logout_redirect_uri" => $base_url.'/gluu_logout.php?gluu_logout=Gluussos',
                    "config_scopes" => ["openid","profile","email"],
                    "gluu_client_id" => "",
                    "gluu_client_secret" => "",
                    "config_acr" => []
                ));
                if($_POST['gluu_users_can_register']==2){
                    $config = json_decode(select_query($db, 'gluu_config'),true);
                    array_push($config['config_scopes'],'permission');
                    $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
                }
                $gluu_config = json_decode(update_query($db, 'gluu_config', $gluu_config),true);
                if(isset($_POST['gluu_client_id']) and !empty($_POST['gluu_client_id']) and
                    isset($_POST['gluu_client_secret']) and !empty($_POST['gluu_client_secret'])){
                    $gluu_config = json_encode(array(
                        "gluu_oxd_port" =>$_POST['gluu_oxd_port'],
                        "admin_email" => $GLOBALS['current_user']->email1,
                        "authorization_redirect_uri" => $base_url.'/gluu.php?gluu_login=Gluussos',
                        "post_logout_redirect_uri" => $base_url.'/gluu_logout.php?gluu_logout=Gluussos',
                        "config_scopes" => ["openid","profile","email"],
                        "gluu_client_id" => $_POST['gluu_client_id'],
                        "gluu_client_secret" => $_POST['gluu_client_secret'],
                        "config_acr" => []
                    ));
                    $gluu_config = json_decode(update_query($db, 'gluu_config', $gluu_config),true);
                    if($_POST['gluu_users_can_register']==2){
                        $config = json_decode(select_query($db, 'gluu_config'),true);
                        array_push($config['config_scopes'],'permission');
                        $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
                    }
                    $register_site = new Register_site();
                    $register_site->setRequestOpHost($gluu_provider);
                    $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                    $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                    $register_site->setRequestContacts([$gluu_config['admin_email']]);
                    $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
                    $get_scopes = json_encode($obj->scopes_supported);
                    if(!empty($obj->acr_values_supported)){
                        $get_acr = json_encode($obj->acr_values_supported);
                        $get_acr = update_query($db, 'gluu_acr', $get_acr);
                        $register_site->setRequestAcrValues($gluu_config['config_acr']);
                    }
                    else{
                        $register_site->setRequestAcrValues($gluu_config['config_acr']);
                    }
                    if(!empty($obj->scopes_supported)){
                        $get_scopes = json_encode($obj->scopes_supported);
                        $get_scopes = update_query($db, 'gluu_scopes', $get_scopes);
                        $register_site->setRequestScope($obj->scopes_supported);
                    }
                    else{
                        $register_site->setRequestScope($gluu_config['config_scopes']);
                    }
                    $register_site->setRequestClientId($gluu_config['gluu_client_id']);
                    $register_site->setRequestClientSecret($gluu_config['gluu_client_secret']);
                    $status = $register_site->request();
                    if ($status['message'] == 'invalid_op_host') {
                        $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                        SugarApplication::redirect('index.php?module=Gluussos&action=general');
                        return;
                    }
                    if (!$status['status']) {
                        $_SESSION['message_error'] = 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.';
                        SugarApplication::redirect('index.php?module=Gluussos&action=general');
                        return;
                    }
                    if ($status['message'] == 'internal_error') {
                        $_SESSION['message_error'] = 'ERROR: '.$status['error_message'];
                        SugarApplication::redirect('index.php?module=Gluussos&action=general');
                        return;
                    }
                    $gluu_oxd_id = $register_site->getResponseOxdId();
                    //var_dump($register_site->getResponseObject());exit;
                    if ($gluu_oxd_id) {
                        $gluu_oxd_id = update_query($db, 'gluu_oxd_id', $gluu_oxd_id);
                        $gluu_provider = $register_site->getResponseOpHost();
                        $gluu_provider = update_query($db, 'gluu_provider', $gluu_provider);

                        $_SESSION['message_success'] = 'Your settings are saved successfully.';
                        SugarApplication::redirect('index.php?module=Gluussos&action=general');
                        return;
                    } else {
                        $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                        SugarApplication::redirect('index.php?module=Gluussos&action=general');
                        return;
                    }
                }
                else{
                    $_SESSION['openid_error'] = 'Error505.';
                    SugarApplication::redirect('index.php?module=Gluussos&action=general');
                    return;
                }
            }
            else{

                $gluu_config = json_encode(array(
                    "gluu_oxd_port" =>$_POST['gluu_oxd_port'],
                    "admin_email" => $GLOBALS['current_user']->email1,
                    "authorization_redirect_uri" => $base_url.'/gluu.php?gluu_login=Gluussos',
                    "post_logout_redirect_uri" => $base_url.'/gluu_logout.php?gluu_logout=Gluussos',
                    "config_scopes" => ["openid","profile","email"],
                    "gluu_client_id" => "",
                    "gluu_client_secret" => "",
                    "config_acr" => []
                ));
                $gluu_config = json_decode(update_query($db, 'gluu_config', $gluu_config),true);
                if($_POST['gluu_users_can_register']==2){
                    $config = json_decode(select_query($db, 'gluu_config'),true);
                    array_push($config['config_scopes'],'permission');
                    $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
                }
                $register_site = new Register_site();
                $register_site->setRequestOpHost($gluu_provider);
                $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                $register_site->setRequestContacts([$gluu_config['admin_email']]);
                $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
                $get_scopes = json_encode($obj->scopes_supported);
                if(!empty($obj->acr_values_supported)){
                    $get_acr = json_encode($obj->acr_values_supported);
                    $get_acr = json_decode(update_query($db, 'gluu_acr', $get_acr));
                    $register_site->setRequestAcrValues($gluu_config['config_acr']);
                }
                else{
                    $register_site->setRequestAcrValues($gluu_config['config_acr']);
                }
                if(!empty($obj->scopes_supported)){
                    $get_scopes = json_encode($obj->scopes_supported);
                    $get_scopes = json_decode(update_query($db, 'gluu_scopes', $get_scopes));
                    $register_site->setRequestScope($obj->scopes_supported);
                }
                else{
                    $register_site->setRequestScope($gluu_config['config_scopes']);
                }
                $status = $register_site->request();
                //var_dump($status);exit;
                if ($status['message'] == 'invalid_op_host') {
                    $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                    SugarApplication::redirect('index.php?module=Gluussos&action=general');
                    return;
                }
                if (!$status['status']) {
                    $_SESSION['message_error'] = 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.';
                    SugarApplication::redirect('index.php?module=Gluussos&action=general');
                    return;
                }
                if ($status['message'] == 'internal_error') {
                    $_SESSION['message_error'] = 'ERROR: '.$status['error_message'];
                    SugarApplication::redirect('index.php?module=Gluussos&action=general');
                    return;
                }
                $gluu_oxd_id = $register_site->getResponseOxdId();
                if ($gluu_oxd_id) {
                    $gluu_oxd_id = update_query($db, 'gluu_oxd_id', $gluu_oxd_id);
                    $gluu_provider = $register_site->getResponseOpHost();
                    $gluu_provider = update_query($db, 'gluu_provider', $gluu_provider);

                    $_SESSION['message_success'] = 'Your settings are saved successfully.';
                    SugarApplication::redirect('index.php?module=Gluussos&action=general');
                    return;
                }
                else {
                    $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                    SugarApplication::redirect('index.php?module=Gluussos&action=general');
                    return;
                }
            }
        }
        else{
            $_SESSION['message_error'] = 'Please enter correct URI of the OpenID Provider';
            SugarApplication::redirect('index.php?module=Gluussos&action=general');
            return;
        }

    }
    else{
        $gluu_config = json_encode(array(
            "gluu_oxd_port" =>$_POST['gluu_oxd_port'],
            "admin_email" => $GLOBALS['current_user']->email1,
            "authorization_redirect_uri" => $base_url.'/gluu.php?gluu_login=Gluussos',
            "post_logout_redirect_uri" => $base_url.'/gluu_logout.php?gluu_logout=Gluussos',
            "config_scopes" => ["openid","profile","email"],
            "gluu_client_id" => "",
            "gluu_client_secret" => "",
            "config_acr" => []
        ));
        $gluu_config = json_decode(update_query($db, 'gluu_config', $gluu_config),true);
        if($_POST['gluu_users_can_register']==2){
            $config = json_decode(select_query($db, 'gluu_config'),true);
            array_push($config['config_scopes'],'permission');
            $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
        }
        $register_site = new Register_site();
        $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
        $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
        $register_site->setRequestContacts([$gluu_config['admin_email']]);
        $register_site->setRequestAcrValues($gluu_config['config_acr']);
        $register_site->setRequestScope($gluu_config['config_scopes']);
        $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
        $status = $register_site->request();

        if ($status['message'] == 'invalid_op_host') {
            $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
            SugarApplication::redirect('index.php?module=Gluussos&action=general');
            return;
        }
        if (!$status['status']) {
            $_SESSION['message_error'] = 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.';
            SugarApplication::redirect('index.php?module=Gluussos&action=general');
            return;
        }
        if ($status['message'] == 'internal_error') {
            $_SESSION['message_error'] = 'ERROR: '.$status['error_message'];
            SugarApplication::redirect('index.php?module=Gluussos&action=general');
            return;
        }
        $gluu_oxd_id = $register_site->getResponseOxdId();
        if ($gluu_oxd_id) {
            $gluu_oxd_id = update_query($db, 'gluu_oxd_id', $gluu_oxd_id);
            $gluu_provider = $register_site->getResponseOpHost();
            $gluu_provider = update_query($db, 'gluu_provider', $gluu_provider);
            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );
            $json = file_get_contents($gluu_provider.'/.well-known/openid-configuration', false, stream_context_create($arrContextOptions));
            $obj = json_decode($json);
            $register_site = new Register_site();
            $register_site->setRequestOpHost($gluu_provider);
            $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
            $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
            $register_site->setRequestContacts([$gluu_config['admin_email']]);
            $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);

            $get_scopes = json_encode($obj->scopes_supported);
            if(!empty($obj->acr_values_supported)){
                $get_acr = json_encode($obj->acr_values_supported);
                $get_acr = update_query($db, 'gluu_acr', $get_acr);
                $register_site->setRequestAcrValues($gluu_config['config_acr']);
            }
            else{
                $register_site->setRequestAcrValues($gluu_config['config_acr']);
            }
            if(!empty($obj->scopes_supported)){
                $get_scopes = json_encode($obj->scopes_supported);
                $get_scopes = update_query($db, 'gluu_scopes', $get_scopes);
                $register_site->setRequestScope($obj->scopes_supported);
            }
            else{
                $register_site->setRequestScope($gluu_config['config_scopes']);
            }
            $status = $register_site->request();
            if ($status['message'] == 'invalid_op_host') {
                $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                SugarApplication::redirect('index.php?module=Gluussos&action=general');
                return;
            }
            if (!$status['status']) {
                $_SESSION['message_error'] = 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.';
                SugarApplication::redirect('index.php?module=Gluussos&action=general');
                return;
            }
            if ($status['message'] == 'internal_error') {
                $_SESSION['message_error'] = 'ERROR: '.$status['error_message'];
                SugarApplication::redirect('index.php?module=Gluussos&action=general');
                return;
            }
            $gluu_oxd_id = $register_site->getResponseOxdId();
            if ($gluu_oxd_id) {
                $gluu_oxd_id = update_query($db, 'gluu_oxd_id', $gluu_oxd_id);
                $_SESSION['message_success'] = 'Your settings are saved successfully.';
                SugarApplication::redirect('index.php?module=Gluussos&action=general');
                return;
            }
            else {
                $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                SugarApplication::redirect('index.php?module=Gluussos&action=general');
                return;
            }
        }
        else {
            $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
            SugarApplication::redirect('index.php?module=Gluussos&action=general');
            return;
        }
    }
}
else if (isset( $_REQUEST['form_key'] ) and strpos( $_REQUEST['form_key'], 'general_oxd_edit' ) !== false) {

    if($_POST['gluu_user_role']){
        update_query($db, 'gluu_user_role', $_POST['gluu_user_role']);
    }else{
        update_query($db, 'gluu_user_role', 0);
    }
    if ($_POST['gluu_users_can_register']==1) {
        update_query($db, 'gluu_users_can_register', $_POST['gluu_users_can_register']);
        if(!empty(array_values(array_filter($_POST['gluu_new_role'])))){
            update_query($db, 'gluu_new_role', json_encode(array_values(array_filter($_POST['gluu_new_role']))));
            $config = json_decode(select_query($db, 'gluu_config'),true);
            array_push($config['config_scopes'],'permission');
            $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
        }else{
            update_query($db, 'gluu_new_role', json_encode(null));
        }
    }
    elseif($_POST['gluu_users_can_register']==2){
        update_query($db, 'gluu_users_can_register', 2);

        if(!empty(array_values(array_filter($_POST['gluu_new_role'])))){
            update_query($db, 'gluu_new_role', json_encode(array_values(array_filter($_POST['gluu_new_role']))));
            $config = json_decode(select_query($db, 'gluu_config'),true);
            array_push($config['config_scopes'],'permission');
            $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
        }else{
            update_query($db, 'gluu_new_role', json_encode(null));
        }
    }
    $get_scopes = json_encode(array("openid", "profile","email"));
    $get_scopes = update_query($db, 'get_scopes', $get_scopes);

    $gluu_acr = json_encode(array("none"));
    $gluu_acr = update_query($db, 'gluu_acr', $gluu_acr);

    if(!isset($_SERVER['HTTPS']) or $_SERVER['HTTPS'] != "on") {
        $_SESSION['message_error'] = 'OpenID Connect requires https. This plugin will not work if your website uses http only.';
        SugarApplication::redirect('index.php?module=Gluussos&action=generalEdit');
        return;
    }
    if (empty($_POST['gluu_oxd_port'])) {
        $_SESSION['message_error'] = 'All the fields are required. Please enter valid entries.';
        SugarApplication::redirect('index.php?module=Gluussos&action=generalEdit');
        return;
    }
    else if (intval($_POST['gluu_oxd_port']) > 65535 && intval($_POST['oxd_port']) < 0) {
        $_SESSION['message_error'] = 'Enter your oxd host port (Min. number 0, Max. number 65535).';
        SugarApplication::redirect('index.php?module=Gluussos&action=generalEdit');
        return;
    }
    if  (!empty($_POST['gluu_custom_logout'])) {
        if (filter_var($_POST['gluu_custom_logout'], FILTER_VALIDATE_URL) === false) {
            $_SESSION['message_error'] = 'Please enter valid Custom URI.';
        }else{
            update_query($db, 'gluu_custom_logout', $_POST['gluu_custom_logout']);
        }
    }else{
        update_query($db, 'gluu_custom_logout', '');
    }
    $gluu_oxd_id = update_query($db, 'gluu_oxd_id', '');
    $gluu_config = array(
        "gluu_oxd_port" =>$_POST['gluu_oxd_port'],
        "admin_email" => $GLOBALS['current_user']->email1,
        "authorization_redirect_uri" => $base_url.'/gluu.php?gluu_login=Gluussos',
        "post_logout_redirect_uri" => $base_url.'/gluu_logout.php?gluu_logout=Gluussos',
        "config_scopes" => ["openid","profile","email"],
        "gluu_client_id" => "",
        "gluu_client_secret" => "",
        "config_acr" => []
    );

    $gluu_config = update_query($db, 'gluu_config', json_encode($gluu_config));
    if($_POST['gluu_users_can_register']==2){
        $config = json_decode(select_query($db, 'gluu_config'),true);
        array_push($config['config_scopes'],'permission');
        $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
    }
    $gluu_provider         = select_query($db, 'gluu_provider');
    if (!empty($gluu_provider)) {
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        $json = file_get_contents($gluu_provider.'/.well-known/openid-configuration', false, stream_context_create($arrContextOptions));
        $obj = json_decode($json);
        if(!empty($obj->userinfo_endpoint)){
            if(empty($obj->registration_endpoint)){
                if(isset($_POST['gluu_client_id']) and !empty($_POST['gluu_client_id']) and
                    isset($_POST['gluu_client_secret']) and !empty($_POST['gluu_client_secret']) and !$obj->registration_endpoint){
                    $gluu_config = array(
                        "gluu_oxd_port" => $_POST['gluu_oxd_port'],
                        "admin_email" => $GLOBALS['current_user']->email1,
                        "gluu_client_id" => $_POST['gluu_client_id'],
                        "gluu_client_secret" => $_POST['gluu_client_secret'],
                        "authorization_redirect_uri" => $base_url.'/gluu.php?gluu_login=Gluussos',
                        "post_logout_redirect_uri" => $base_url.'/gluu_logout.php?gluu_logout=Gluussos',
                        "config_scopes" => ["openid", "profile","email"],
                        "config_acr" => []
                    );
                    $gluu_config1 = update_query($db, 'gluu_config', json_encode($gluu_config));
                    if($_POST['gluu_users_can_register']==2){
                        $config = json_decode(select_query($db, 'gluu_config'),true);
                        array_push($config['config_scopes'],'permission');
                        $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
                    }
                    $register_site = new Register_site();
                    $register_site->setRequestOpHost($gluu_provider);
                    $register_site->setRequestAcrValues($gluu_config['config_acr']);
                    $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                    $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                    $register_site->setRequestContacts([$GLOBALS['current_user']->email1]);
                    $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
                    if(!empty($obj->acr_values_supported)){
                        $get_acr = json_encode($obj->acr_values_supported);
                        $gluu_config = update_query($db, 'gluu_acr', $gluu_acr);
                    }
                    if(!empty($obj->scopes_supported)){
                        $get_scopes = json_encode($obj->scopes_supported);
                        $gluu_config = update_query($db, 'get_scopes', $get_scopes);
                        $register_site->setRequestScope($obj->scopes_supported);
                    }else{
                        $register_site->setRequestScope($gluu_config['config_scopes']);
                    }
                    $register_site->setRequestClientId($_POST['gluu_client_id']);
                    $register_site->setRequestClientSecret($_POST['gluu_client_secret']);
                    $status = $register_site->request();
                    if ($status['message'] == 'invalid_op_host') {
                        $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                        SugarApplication::redirect('index.php?module=Gluussos&action=generalEdit');
                        return;
                    }
                    if (!$status['status']) {
                        $_SESSION['message_error'] = 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.';
                        SugarApplication::redirect('index.php?module=Gluussos&action=generalEdit');
                        return;
                    }
                    if ($status['message'] == 'internal_error') {
                        $_SESSION['message_error'] = 'ERROR: '.$status['error_message'];
                        SugarApplication::redirect('index.php?module=Gluussos&action=generalEdit');
                        return;
                    }
                    $gluu_oxd_id = $register_site->getResponseOxdId();
                    if ($gluu_oxd_id) {
                        $gluu_oxd_id = update_query($db, 'gluu_oxd_id', $gluu_oxd_id);
                        $gluu_provider = $register_site->getResponseOpHost();
                        $gluu_provider = update_query($db, 'gluu_provider', $gluu_provider);

                        $_SESSION['message_success'] = 'Your settings are saved successfully.';
                        SugarApplication::redirect('index.php?module=Gluussos&action=general');
                        return;
                    } else {
                        $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                        SugarApplication::redirect('index.php?module=Gluussos&action=general');
                        return;
                    }
                }
                else{
                    $_SESSION['openid_error_edit'] = 'Error506';
                    SugarApplication::redirect('index.php?module=Gluussos&action=generalEdit');
                    return;
                }
            }
            else{
                $gluu_config = array(
                    "gluu_oxd_port" =>$_POST['gluu_oxd_port'],
                    "admin_email" => $GLOBALS['current_user']->email1,
                    "authorization_redirect_uri" => $base_url.'/gluu.php?gluu_login=Gluussos',
                    "post_logout_redirect_uri" => $base_url.'/gluu_logout.php?gluu_logout=Gluussos',
                    "config_scopes" => ["openid","profile","email"],
                    "gluu_client_id" => "",
                    "gluu_client_secret" => "",
                    "config_acr" => []
                );
                $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($gluu_config)),true);
                if($_POST['gluu_users_can_register']==2){
                    $config = json_decode(select_query($db, 'gluu_config'),true);
                    array_push($config['config_scopes'],'permission');
                    $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
                }
                $register_site = new Register_site();
                $register_site->setRequestOpHost($gluu_provider);
                $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
                $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
                $register_site->setRequestContacts([$gluu_config['admin_email']]);
                $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
                $get_scopes = json_encode($obj->scopes_supported);
                if(!empty($obj->acr_values_supported)){
                    $get_acr = json_encode($obj->acr_values_supported);
                    $get_acr = json_decode(update_query($db, 'gluu_acr', $get_acr));
                    $register_site->setRequestAcrValues($gluu_config['config_acr']);
                }
                else{
                    $register_site->setRequestAcrValues($gluu_config['config_acr']);
                }
                if(!empty($obj->scopes_supported)){
                    $get_scopes = json_encode($obj->scopes_supported);
                    $get_scopes = json_decode(update_query($db, 'gluu_scopes', $get_scopes));
                    $register_site->setRequestScope($obj->scopes_supported);
                }
                else{
                    $register_site->setRequestScope($gluu_config['config_scopes']);
                }
                $status = $register_site->request();
                //var_dump($status);exit;
                if ($status['message'] == 'invalid_op_host') {
                    $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                    SugarApplication::redirect('index.php?module=Gluussos&action=general');
                    return;
                }
                if (!$status['status']) {
                    $_SESSION['message_error'] = 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.';
                    SugarApplication::redirect('index.php?module=Gluussos&action=general');
                    return;
                }
                if ($status['message'] == 'internal_error') {
                    $_SESSION['message_error'] = 'ERROR: '.$status['error_message'];
                    SugarApplication::redirect('index.php?module=Gluussos&action=general');
                    return;
                }
                $gluu_oxd_id = $register_site->getResponseOxdId();
                if ($gluu_oxd_id) {
                    $gluu_oxd_id = update_query($db, 'gluu_oxd_id', $gluu_oxd_id);
                    $gluu_provider = $register_site->getResponseOpHost();
                    $gluu_provider = update_query($db, 'gluu_provider', $gluu_provider);

                    $_SESSION['message_success'] = 'Your settings are saved successfully.';
                    SugarApplication::redirect('index.php?module=Gluussos&action=general');
                    return;
                }
                else {
                    $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                    SugarApplication::redirect('index.php?module=Gluussos&action=general');
                    return;
                }
            }
        }
        else{
            $_SESSION['message_error'] = 'Please enter correct URI of the OpenID Provider';
            SugarApplication::redirect('index.php?module=Gluussos&action=generalEdit');
            return;
        }
    }
    else{
        $gluu_config = array(
            "gluu_oxd_port" =>$_POST['gluu_oxd_port'],
            "admin_email" => $GLOBALS['current_user']->email1,
            "authorization_redirect_uri" => $base_url.'/gluu.php?gluu_login=Gluussos',
            "post_logout_redirect_uri" => $base_url.'/gluu_logout.php?gluu_logout=Gluussos',
            "config_scopes" => ["openid","profile","email"],
            "gluu_client_id" => "",
            "gluu_client_secret" => "",
            "config_acr" => []
        );
        $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($gluu_config)),true);
        if($_POST['gluu_users_can_register']==2){
            $config = json_decode(select_query($db, 'gluu_config'),true);
            array_push($config['config_scopes'],'permission');
            $gluu_config = json_decode(update_query($db, 'gluu_config', json_encode($config)),true);
        }
        $register_site = new Register_site();
        $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
        $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
        $register_site->setRequestContacts([$gluu_config['admin_email']]);
        $register_site->setRequestAcrValues($gluu_config['config_acr']);
        $register_site->setRequestScope($gluu_config['config_scopes']);
        $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
        $status = $register_site->request();

        if ($status['message'] == 'invalid_op_host') {
            $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
            SugarApplication::redirect('index.php?module=Gluussos&action=general');
            return;
        }
        if (!$status['status']) {
            $_SESSION['message_error'] = 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.';
            SugarApplication::redirect('index.php?module=Gluussos&action=general');
            return;
        }
        if ($status['message'] == 'internal_error') {
            $_SESSION['message_error'] = 'ERROR: '.$status['error_message'];
            SugarApplication::redirect('index.php?module=Gluussos&action=general');
            return;
        }
        $gluu_oxd_id = $register_site->getResponseOxdId();
        if ($gluu_oxd_id) {
            $gluu_oxd_id = update_query($db, 'gluu_oxd_id', $gluu_oxd_id);
            $gluu_provider = $register_site->getResponseOpHost();
            $gluu_provider = update_query($db, 'gluu_provider', $gluu_provider);
            $arrContextOptions=array(
                "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ),
            );
            $json = file_get_contents($gluu_provider.'/.well-known/openid-configuration', false, stream_context_create($arrContextOptions));
            $obj = json_decode($json);
            $register_site = new Register_site();
            $register_site->setRequestOpHost($gluu_provider);
            $register_site->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
            $register_site->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
            $register_site->setRequestContacts([$gluu_config['admin_email']]);
            $register_site->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);

            $get_scopes = json_encode($obj->scopes_supported);
            if(!empty($obj->acr_values_supported)){
                $get_acr = json_encode($obj->acr_values_supported);
                $get_acr = update_query($db, 'gluu_acr', $get_acr);
                $register_site->setRequestAcrValues($gluu_config['config_acr']);
            }
            else{
                $register_site->setRequestAcrValues($gluu_config['config_acr']);
            }
            if(!empty($obj->scopes_supported)){
                $get_scopes = json_encode($obj->scopes_supported);
                $get_scopes = update_query($db, 'gluu_scopes', $get_scopes);
                $register_site->setRequestScope($obj->scopes_supported);
            }
            else{
                $register_site->setRequestScope($gluu_config['config_scopes']);
            }
            $status = $register_site->request();
            if ($status['message'] == 'invalid_op_host') {
                $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                SugarApplication::redirect('index.php?module=Gluussos&action=general');
                return;
            }
            if (!$status['status']) {
                $_SESSION['message_error'] = 'Can not connect to the oxd server. Please check the oxd-config.json file to make sure you have entered the correct port and the oxd server is operational.';
                SugarApplication::redirect('index.php?module=Gluussos&action=general');
                return;
            }
            if ($status['message'] == 'internal_error') {
                $_SESSION['message_error'] = 'ERROR: '.$status['error_message'];
                SugarApplication::redirect('index.php?module=Gluussos&action=general');
                return;
            }
            $gluu_oxd_id = $register_site->getResponseOxdId();
            if ($gluu_oxd_id) {
                $gluu_oxd_id = update_query($db, 'gluu_oxd_id', $gluu_oxd_id);
                $_SESSION['message_success'] = 'Your settings are saved successfully.';
                SugarApplication::redirect('index.php?module=Gluussos&action=general');
                return;
            }
            else {
                $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
                SugarApplication::redirect('index.php?module=Gluussos&action=general');
                return;
            }
        }
        else {
            $_SESSION['message_error'] = 'ERROR: OpenID Provider host is required if you don\'t provide it in oxd-default-site-config.json';
            SugarApplication::redirect('index.php?module=Gluussos&action=general');
            return;
        }
    }
}
else if( isset( $_REQUEST['form_key'] ) and strpos( $_REQUEST['form_key'], 'general_oxd_id_reset' )  !== false and !empty($_REQUEST['resetButton'])) {
    $db->query("DROP TABLE IF EXISTS `gluu_table`;");
    unset($_SESSION['openid_error']);
    $_SESSION['message_success'] = 'Configurations deleted Successfully.';
    SugarApplication::redirect('index.php?module=Gluussos&action=general');
}
else if( isset( $_REQUEST['form_key'] ) and strpos( $_REQUEST['form_key'], 'openid_config_page' ) !== false ) {
    $params = $_REQUEST;
    $message_success = '';
    $message_error = '';

    if($_POST['send_user_type']){
        $gluu_auth_type = $_POST['send_user_type'];
        $gluu_auth_type = update_query($db, 'gluu_auth_type', $gluu_auth_type);
    }else{
        $gluu_auth_type = update_query($db, 'gluu_auth_type', 'default');
    }
    $gluu_send_user_check = $_POST['send_user_check'];
    $gluu_send_user_check = update_query($db, 'gluu_send_user_check', $gluu_send_user_check);

    if(!empty($params['scope']) && isset($params['scope'])){
        $gluu_config =   json_decode(select_query($db, "gluu_config"),true);
        $gluu_config['config_scopes'] = $params['scope'];
        $gluu_config = json_encode($gluu_config);
        $gluu_config = json_decode(update_query($db, 'gluu_config', $gluu_config),true);
    }
    if(!empty($params['scope_name']) && isset($params['scope_name'])){
        $get_scopes =   json_decode(select_query($db, "gluu_scopes"),true);
        foreach($params['scope_name'] as $scope){
            if($scope && !in_array($scope,$get_scopes)){
                array_push($get_scopes, $scope);
            }
        }
        $get_scopes = json_encode($get_scopes);
        $get_scopes = json_decode(update_query($db, 'gluu_scopes', $get_scopes),true);
    }
    $gluu_acr              = json_decode(select_query($db, 'gluu_acr'),true);

    if(!empty($params['acr_name']) && isset($params['acr_name'])){
        $get_acr =   json_decode(select_query($db, "gluu_acr"),true);
        foreach($params['acr_name'] as $scope){
            if($scope && !in_array($scope,$get_acr)){
                array_push($get_acr, $scope);
            }
        }
        $get_acr = json_encode($get_acr);
        $get_acr = json_decode(update_query($db, 'gluu_acr', $get_acr),true);
    }
    $gluu_config =   json_decode(select_query($db, "gluu_config"),true);
    $gluu_oxd_id =   select_query($db, "gluu_oxd_id");
    $update_site_registration = new Update_site_registration();
    $update_site_registration->setRequestOxdId($gluu_oxd_id);
    $update_site_registration->setRequestAcrValues($gluu_config['acr_values']);
    $update_site_registration->setRequestAuthorizationRedirectUri($gluu_config['authorization_redirect_uri']);
    $update_site_registration->setRequestLogoutRedirectUri($gluu_config['post_logout_redirect_uri']);
    $update_site_registration->setRequestContacts([$gluu_config['admin_email']]);
    $update_site_registration->setRequestClientLogoutUri($gluu_config['post_logout_redirect_uri']);
    $update_site_registration->setRequestScope($gluu_config['config_scopes']);
    $status = $update_site_registration->request();
    $new_oxd_id = $update_site_registration->getResponseOxdId();
    if($new_oxd_id){
        $get_scopes = update_query($db, 'gluu_oxd_id', $new_oxd_id);
    }


    $_SESSION['message_success'] = 'Your OpenID connect configuration has been saved.';
    $_SESSION['message_error'] = $message_error;
    SugarApplication::redirect('index.php?module=Gluussos&action=openidconfig');
    exit;
}

?>
