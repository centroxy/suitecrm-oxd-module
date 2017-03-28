<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
$db = DBManagerFactory::getInstance();
function update_query($db, $action, $value){
    $db->query("UPDATE `gluu_table` SET `gluu_value` = '$value' WHERE `gluu_action` LIKE '$action';");
    $result = $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE '$action'"))["gluu_value"];
    return $result;
}
    if( isset( $_POST['form_key_scope_delete'] ) and strpos( $_POST['form_key_scope_delete'], 'form_key_scope_delete' ) !== false ) {
        $get_scopes =   json_decode(select_query($db, 'gluu_scopes'),true);
        $up_cust_sc =  array();
        foreach($get_scopes as $custom_scop){
            if($custom_scop !=$_POST['delete_scope']){
                array_push($up_cust_sc,$custom_scop);
            }
        }
        $get_scopes = json_encode($up_cust_sc);
        $get_scopes = update_query($db, 'gluu_scopes', $get_scopes);


        $gluu_config =   json_decode(select_query($db, "gluu_config"),true);
        $up_cust_scope =  array();
        foreach($gluu_config['config_scopes'] as $custom_scop){
            if($custom_scop !=$_POST['delete_scope']){
                array_push($up_cust_scope,$custom_scop);
            }
        }
        $gluu_config['config_scopes'] = $up_cust_scope;
        $gluu_config = json_encode($gluu_config);
        $gluu_config = json_decode(update_query($db, 'gluu_config', $gluu_config),true);
        return true;
    }
    else if (isset($_POST['form_key_scope']) and strpos( $_POST['form_key_scope'], 'oxd_openid_config_new_scope' ) !== false) {
        if (gluu_is_oxd_registered()) {
            if (!empty($_POST['new_value_scope']) && isset($_POST['new_value_scope'])) {

                $get_scopes =   json_decode(select_query($db, "gluu_scopes"),true);
                if($_POST['new_value_scope'] && !in_array($_POST['new_value_scope'],$get_scopes)){
                    array_push($get_scopes, $_POST['new_value_scope']);
                }
                $get_scopes = json_encode($get_scopes);
                update_query($db, 'gluu_scopes', $get_scopes);
                return true;
            }

        }
    }
    else if( isset( $_REQUEST['form_key'] ) and strpos( $_REQUEST['form_key'], 'openid_config_page' ) !== false ) {
        $params = $_REQUEST;
        if(!empty($params['scope']) && isset($params['scope'])){
            $gluu_config =   json_decode(select_query($db, "gluu_config"),true);
            $gluu_config['config_scopes'] = $params['scope'];
            $gluu_config = json_encode($gluu_config);
            $gluu_config = json_decode(update_query($db, 'gluu_config', $gluu_config),true);
            return true;
        }
    }
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
$query = "CREATE TABLE IF NOT EXISTS `gluu_table` (
  `gluu_action` varchar(255) NOT NULL,
  `gluu_value` longtext NOT NULL,
  UNIQUE(`gluu_action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
$result = $db->query($query);
function select_query($db, $action){
    $result = $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE '$action'"))["gluu_value"];
    return $result;
}
function insert_query($db, $action, $value){
    $result = $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('$action','$value')");
    return $result;
}
if(!select_query($db, 'gluu_scopes')){
    $get_scopes = json_encode(array("openid", "profile","email"));
    $result = insert_query($db, 'gluu_scopes', $get_scopes);
}
if(!select_query($db, 'gluu_acr')){
    $custom_scripts = json_encode(array('none'));
    $result = insert_query($db, 'gluu_acr', $custom_scripts);
}
if(!select_query($db, 'gluu_config')){
    $gluu_config = json_encode(array(
        "gluu_oxd_port" =>8099,
        "admin_email" => $GLOBALS['current_user']->email1,
        "authorization_redirect_uri" => $base_url.'gluu.php?gluu_login=Gluussos',
        "post_logout_redirect_uri" => $base_url.'gluu_logout.php?gluu_logout=Gluussos',
        "config_scopes" => ["openid","profile","email"],
        "gluu_client_id" => "",
        "gluu_client_secret" => "",
        "config_acr" => []
    ));
    $result = insert_query($db, 'gluu_config', $gluu_config);
}
if(!select_query($db, 'gluu_auth_type')){
    $gluu_auth_type = 'default';
    $result = insert_query($db, 'gluu_auth_type', $gluu_auth_type);
}
if(!select_query($db, 'gluu_custom_logout')){
    $gluu_custom_logout = '';
    $result = insert_query($db, 'gluu_custom_logout', $gluu_custom_logout);
}
if(!select_query($db, 'gluu_provider')){
    $gluu_provider = '';
    $result = insert_query($db, 'gluu_provider', $gluu_provider);
}
if(!select_query($db, 'gluu_send_user_check')){
    $gluu_send_user_check = 1;
    $result = insert_query($db, 'gluu_send_user_check', $gluu_send_user_check);
}
if(!select_query($db, 'gluu_oxd_id')){
    $gluu_oxd_id = '';
    $result = insert_query($db, 'gluu_oxd_id', $gluu_oxd_id);
}
if(!select_query($db, 'gluu_user_role')){
    $gluu_user_role = 0;
    $result = insert_query($db, 'gluu_user_role', $gluu_user_role);
}
if(!select_query($db, 'gluu_users_can_register')){
    $gluu_users_can_register = 1;
    $result = insert_query($db, 'gluu_users_can_register', $gluu_users_can_register);
}
if(!select_query($db, 'gluu_new_role')){
    $gluu_users_can_register = 1;
    $result = insert_query($db, 'gluu_new_role', null);
}
$get_scopes                 = json_decode(select_query($db, 'gluu_scopes'),true);
$gluu_config                = json_decode(select_query($db, 'gluu_config'),true);
$gluu_acr                   = json_decode(select_query($db, 'gluu_acr'),true);
$gluu_auth_type             = select_query($db, 'gluu_auth_type');
$gluu_send_user_check       = select_query($db, 'gluu_send_user_check');
$gluu_provider              = select_query($db, 'gluu_provider');
$gluu_user_role             = select_query($db, 'gluu_user_role');
$gluu_custom_logout         = select_query($db, 'gluu_custom_logout');
$gluu_new_roles              = json_decode(select_query($db, 'gluu_new_role'));
$gluu_users_can_register    = select_query($db, 'gluu_users_can_register');
function gluu_is_oxd_registered(){
    $db = DBManagerFactory::getInstance();
    if(select_query($db, 'gluu_oxd_id')){
        $oxd_id = select_query($db, 'gluu_oxd_id');
        if(!$oxd_id ) {
            return 0;
        } else {
            return $oxd_id;
        }
    }
}
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="modules/Gluussos/GluuOxd_Openid/js/scope-custom-script.js"></script>
<script type="application/javascript">
    jQuery(document).ready(function() {

        jQuery('[data-toggle="tooltip"]').tooltip();
        jQuery('#p_role').on('click', 'a.remrole', function() {
            jQuery(this).parents('.role_p').remove();
        });

    });
    jQuery(document ).ready(function() {

        jQuery("input[name='scope[]']").change(function(){
            var form=$("#scpe_update");
            if (jQuery(this).is(':checked')) {
                jQuery.ajax({
                    url: window.location,
                    type: 'POST',
                    data:form.serialize(),
                    success: function(result){
                        if(result){
                            return false;
                        }
                    }});
            }else{
                jQuery.ajax({
                    url: window.location,
                    type: 'POST',
                    data:form.serialize(),
                    success: function(result){
                        if(result){
                            return false;
                        }
                    }});
            }
        });

    });
    function delete_scopes(val){
        if (confirm("Are you sure that you want to delete this scope? You will no longer be able to request this user information from the OP.")) {
            jQuery.ajax({
                url: window.location,
                type: 'POST',
                data:{form_key_scope_delete:'form_key_scope_delete', delete_scope:val},
                success: function(result){
                    location.reload();
                }});
        }
        else{
            return false;
        }

    }

    function add_scope_for_delete() {
        var striped = jQuery('#table-striped');
        var k = jQuery('#p_scents p').size() + 1;
        var new_scope_field = jQuery('#new_scope_field').val();
        var m = true;
        if(new_scope_field){
            jQuery("input[name='scope[]']").each(function(){
                // get name of input
                var value =  jQuery(this).attr("value");
                if(value == new_scope_field){
                    m = false;
                }
            });
            if(m){
                jQuery('<tr >' +
                    '<td style="padding: 0px !important;">' +
                    '   <p  id="'+new_scope_field+'">' +
                    '     <input type="checkbox" name="scope[]" id="new_'+new_scope_field+'" value="'+new_scope_field+'"  />'+
                    '   </p>' +
                    '</td>' +
                    '<td style="padding: 0px !important;">' +
                    '   <p  id="'+new_scope_field+'">' +
                    new_scope_field+
                    '   </p>' +
                    '</td>' +
                    '<td style="padding: 0px !important; ">' +
                    '   <a href="#scop_section" class="btn btn-danger btn-xs" style="margin: 5px; float: right" onclick="delete_scopes(\''+new_scope_field+'\')" >' +
                    '<span class="glyphicon glyphicon-trash"></span>' +
                    '</a>' +
                    '</td>' +
                    '</tr>').appendTo(striped);
                jQuery('#new_scope_field').val('');

                jQuery.ajax({
                    url: window.location,
                    type: 'POST',
                    data:{form_key_scope:'oxd_openid_config_new_scope', new_value_scope:new_scope_field},
                    success: function(result){
                        if(result){
                            return false;
                        }
                    }});
                jQuery("#new_"+new_scope_field).change(
                    function(){
                        var form=$("#scpe_update");
                        if (jQuery(this).is(':checked')) {
                            jQuery.ajax({
                                url: window.location,
                                type: 'POST',
                                data:form.serialize(),
                                success: function(result){
                                    if(result){
                                        return false;
                                    }
                                }});
                        }else{
                            jQuery.ajax({
                                url: window.location,
                                type: 'POST',
                                data:form.serialize(),
                                success: function(result){
                                    if(result){
                                        return false;
                                    }
                                }});
                        }
                    });

                return false;
            }
            else{
                alert('The scope named '+new_scope_field+' is exist!');
                jQuery('#new_scope_field').val('');
                return false;
            }
        }else{
            alert('Please input scope name!');
            jQuery('#new_scope_field').val('');
            return false;
        }
    }
</script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<link href="modules/Gluussos/GluuOxd_Openid/css/gluu-oxd-css.css" rel="stylesheet"/>
<div class="mo2f_container">
    <div class="container">
        <div id="messages">
            <?php if (!empty($_SESSION['message_error'])){ ?>
                <div class="mess_red_error">
                    <?php echo $_SESSION['message_error']; ?>
                </div>
                <?php unset($_SESSION['message_error']);} ?>
            <?php if (!empty($_SESSION['message_success'])) { ?>
                <div class="mess_green">
                    <?php echo $_SESSION['message_success']; ?>
                </div>
                <?php unset($_SESSION['message_success']);} ?>
        </div>
        <ul class="navbar navbar-tabs">
            <li id="account_setup"><a href="index.php?module=Gluussos&action=general">General</a></li>
            <?php if ( !gluu_is_oxd_registered()) {?>
                <li class="active" id="social-sharing-setup"><button disabled >OpenID Connect Configuration</button></li>
            <?php }else {?>
                <li class="active" id="social-sharing-setup"><a href="index.php?module=Gluussos&action=openidconfig">OpenID Connect Configuration</a></li>
            <?php }?>
            <li id=""><a data-method="#configopenid" href="https://gluu.org/docs/oxd/3.0.1/plugin/suitecrm/" target="_blank">Documentation</a></li>
        </ul>
        <div class="container-page">
            <div id="configopenid" style="padding: 20px !important;">
                <form action="index.php?module=Gluussos&action=gluuPostData" method="post" id="scpe_update">
                    <input type="hidden" name="form_key" value="openid_config_page"/>
                    <fieldset style="border: 2px solid #53cc6b; padding: 20px">
                        <legend style="border-bottom:none; width: 110px !important;">
                            <img style=" height: 45px;" src="modules/Gluussos/GluuOxd_Openid/images/icons/gl.png"/>
                        </legend>
                        <h1 style="margin-left: 30px;padding-bottom: 20px; border-bottom: 2px solid black; width: 75% ">User Scopes</h1>
                        <div >
                            <table style="margin-left: 30px" class="form-table">
                                <tr style="border-bottom: 1px solid green !important;">
                                    <th style="width: 200px; padding: 0px">
                                        <p style="text-align: left;" id="scop_section">
                                            Requested scopes
                                            <a data-toggle="tooltip" class="tooltipLink" data-original-title="Scopes are bundles of attributes that the OP stores about each user. It is recommended that you request the minimum set of scopes required">
                                                <span class="glyphicon glyphicon-info-sign"></span>
                                            </a>
                                        </p>
                                    </th>
                                    <td style="width: 200px; padding-left: 10px !important">
                                            <table id="table-striped" class="form-table" >
                                                <tbody style="width: inherit !important;">
                                                <tr style="padding: 0px">
                                                    <td style="padding: 0px !important; width: 10%">
                                                        <p >
                                                            <input checked type="checkbox" name=""  id="openid" value="openid"  disabled />

                                                        </p>
                                                    </td>
                                                    <td style="padding: 0px !important; width: 70%">
                                                        <p >
                                                            <input type="hidden"  name="scope[]"  value="openid" />openid
                                                        </p>
                                                    </td>
                                                    <td style="padding: 0px !important;  width: 20%">
                                                        <a class="btn btn-danger btn-xs" style="margin: 5px; float: right" disabled><span class="glyphicon glyphicon-trash"></span></a>
                                                    </td>
                                                </tr>
                                                <tr style="padding: 0px">
                                                    <td style="padding: 0px !important; width: 10%">
                                                        <p >
                                                            <input checked type="checkbox" name=""  id="email" value="email"  disabled />

                                                        </p>
                                                    </td>
                                                    <td style="padding: 0px !important; width: 70%">
                                                        <p >
                                                            <input type="hidden"  name="scope[]"  value="email" />email
                                                        </p>
                                                    </td>
                                                    <td style="padding: 0px !important;  width: 20%">
                                                        <a class="btn btn-danger btn-xs" style="margin: 5px; float: right" disabled><span class="glyphicon glyphicon-trash"></span></a>
                                                    </td>
                                                </tr>
                                                <tr style="padding: 0px">
                                                    <td style="padding: 0px !important; width: 10%">
                                                        <p >
                                                            <input checked type="checkbox" name=""  id="profile" value="profile"  disabled />

                                                        </p>
                                                    </td>
                                                    <td style="padding: 0px !important; width: 70%">
                                                        <p >
                                                            <input type="hidden"  name="scope[]"  value="profile" />profile
                                                        </p>
                                                    </td>
                                                    <td style="padding: 0px !important;  width: 20%">
                                                        <a class="btn btn-danger btn-xs" style="margin: 5px; float: right" disabled><span class="glyphicon glyphicon-trash"></span></a>
                                                    </td>
                                                </tr>
                                                <?php foreach($get_scopes as $scop) :?>
                                                    <?php if ($scop == 'openid' or $scop == 'email' or $scop == 'profile'){?>
                                                    <?php } else{?>
                                                        <tr style="padding: 0px">
                                                            <td>
                                                                <p id="<?php echo $scop;?>1">
                                                                    <input <?php if($gluu_config && in_array($scop, $gluu_config['config_scopes'])){ echo "checked";} ?> type="checkbox" name="scope[]"  id="<?php echo $scop;?>1" value="<?php echo $scop;?>" <?php if (!gluu_is_oxd_registered() || $scop=='openid') echo ' disabled '; ?> />
                                                                </p>
                                                            </td>
                                                            <td>
                                                                <p id="<?php echo $scop;?>1">
                                                                    <?php echo $scop;?>
                                                                </p>
                                                            </td>
                                                            <td style="padding: 0px !important; ">
                                                                <button type="button" class="btn btn-danger btn-xs" style="margin: 5px; float: right" onclick="delete_scopes('<?php echo $scop;?>')" ><span class="glyphicon glyphicon-trash"></span></button>
                                                            </td>
                                                        </tr>
                                                    <?php } endforeach;?>
                                                </tbody>
                                            </table>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid green !important;">
                                    <th>
                                        <p style="text-align: left;" id="scop_section1">
                                            Add scopes
                                        </p>
                                    </th>
                                    <td>
                                        <div style="margin-left: 10px" id="p_scents">
                                            <p>
                                                <input <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> class="form-control" type="text" id="new_scope_field" name="new_scope[]" placeholder="Input scope name" />
                                            </p>
                                            <br/>
                                            <p>
                                                <input type="button" style="width: 80px" class="btn btn-primary btn-large" onclick="add_scope_for_delete()" value="Add" id="add_new_scope"/>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <br/>
                        <h1 style="margin-left: 30px;padding-bottom: 20px; border-bottom: 2px solid black; width: 75%">Authentication</h1>
                        <br/>
                        <p style=" margin-left: 20px; font-weight:bold "><label style="display: inline !important; "><input type="checkbox" name="send_user_check" id="send_user" value="1" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> <?php if( $gluu_send_user_check) echo 'checked';?> /> <span>Bypass the local SuiteCRM login page and send users straight to the OP for authentication</span></label>
                        </p>
                        <br/>
                        <div>
                            <table style="margin-left: 30px" class="form-table">
                                <tbody>
                                <tr>
                                    <th style="width: 200px; padding: 0px; ">
                                        <p style="text-align: left;" id="scop_section">
                                            Select ACR: <a data-toggle="tooltip" class="tooltipLink" data-original-title="The OpenID Provider may make available multiple authentication mechanisms. To signal which type of authentication should be used for access to this site you can request a specific ACR. To accept the OP's default authentication, set this field to none.">
                                                <span class="glyphicon glyphicon-info-sign"></span>
                                            </a>
                                        </p>
                                    </th>
                                    <td >
                                        <?php
                                        $custom_scripts = $gluu_acr;
                                        if(!empty($custom_scripts)){
                                            ?>
                                            <select style="margin-left: 5px; padding: 0px !important;" class="form-control" name="send_user_type" id="send_user_type" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>
                                                <option value="default">none</option>
                                                <?php
                                                if($custom_scripts){
                                                    foreach($custom_scripts as $custom_script){
                                                        if($custom_script != "default" and $custom_script != "none"){
                                                            ?>
                                                            <option <?php if($gluu_auth_type == $custom_script) echo 'selected'; ?> value="<?php echo $custom_script;?>"><?php echo $custom_script;?></option>
                                                            <?php
                                                        }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th >
                                        <input type="submit" class="btn btn-primary btn-large" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?> value="Save Authentication Settings" name="set_oxd_config" />
                                    </th>
                                    <td>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>

