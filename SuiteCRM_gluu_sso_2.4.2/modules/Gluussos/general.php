<?php

$base_url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];

$db = DBManagerFactory::getInstance();

$query = "CREATE TABLE IF NOT EXISTS `gluu_table` (

  `gluu_action` varchar(255) NOT NULL,
  `gluu_value` longtext NOT NULL,
  UNIQUE(`gluu_action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";
;
$result = $db->query($query);
if($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'scopes'")){
    $get_scopes = json_encode(array("openid","profile","email","address","clientinfo","mobile_phone","phone"));
    $result = $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('scopes','$get_scopes')");
}
if($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'custom_scripts'")){
    $custom_scripts = json_encode(array(
        array('name'=>'Google','image'=>'modules/Gluussos/GluuOxd_Openid/images/icons/google.png','value'=>'gplus'),
        array('name'=>'Basic','image'=>'modules/Gluussos/GluuOxd_Openid/images/icons/basic.png','value'=>'basic'),
        array('name'=>'Duo','image'=>'modules/Gluussos/GluuOxd_Openid/images/icons/duo.png','value'=>'duo'),
        array('name'=>'U2F token','image'=>'modules/Gluussos/GluuOxd_Openid/images/icons/u2f.png','value'=>'u2f')
    ));
    $result = $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('custom_scripts','$custom_scripts')");
}
if($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'oxd_config'")){
    $oxd_config = json_encode(array(
        "oxd_host_ip" => '127.0.0.1',
        "oxd_host_port" =>8099,
        "admin_email" => $GLOBALS['current_user']->email1,
        "authorization_redirect_uri" => $base_url.'/gluu.php?gluu_login=Gluussos',
        "logout_redirect_uri" => $base_url.'/gluu_logout.php?gluu_login=Gluussos',
        "scope" => ["openid","profile","email","address","clientinfo","mobile_phone","phone"],
        "grant_types" =>["authorization_code"],
        "response_types" => ["code"],
        "application_type" => "web",
        "redirect_uris" => [ $base_url.'/gluu.php?gluu_login=Gluussos' ],
        "acr_values" => [],
    ));
    $result = $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('oxd_config','$oxd_config')");
}
if($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'iconSpace'")){
    $iconSpace = '10';
    $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('iconSpace','$iconSpace')");
}
if($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'iconCustomSize'")){
    $iconCustomSize = '50';
    $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('iconCustomSize','$iconCustomSize')");
}
if($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'iconCustomWidth'")){
    $iconCustomWidth = '200';
    $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('iconCustomWidth','$iconCustomWidth')");
}
if($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'iconCustomHeight'")){
    $iconCustomHeight = '35';
    $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('iconCustomWidth','$iconCustomHeight')");
}
if($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'loginCustomTheme'")){
    $loginCustomTheme = 'default';
    $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('loginCustomTheme','$iconCustomHeight')");
}
if($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'loginTheme'")){
    $loginTheme = 'oval';
    $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('loginTheme','$loginTheme')");
}
if($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'iconCustomColor'")){
    $iconCustomColor = '#0000FF';
    $db->query("INSERT INTO gluu_table (gluu_action, gluu_value) VALUES ('iconCustomColor','$iconCustomColor')");
}
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


if($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'oxd_id'")){
    $oxd_id = $db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE 'oxd_id'"))["gluu_value"];
}
?>
<link href="modules/Gluussos/GluuOxd_Openid/css/gluu-oxd-css.css" rel="stylesheet"/>
<link href="modules/Gluussos/GluuOxd_Openid/css/font-awesome.min.css" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="modules/Gluussos/GluuOxd_Openid/js/scope-custom-script.js"></script>
<script>
    var $m = jQuery.noConflict();
    $m(document).ready(function () {
        $oxd_id = "<?php echo $oxd_id; ?>";
        if ($oxd_id) {
            voiddisplay("#socialsharing");
            setactive('social-sharing-setup');
        } else {
            setactive('account_setup');
        }
        $m(".navbar a").click(function () {
            $id = $m(this).parent().attr('id');
            setactive($id);
            $href = $m(this).data('method');
            voiddisplay($href);
        });

        $m('#error-cancel').click(function () {
            $error = "";
            $m(".error-msg").css("display", "none");
        });
        $m('#success-cancel').click(function () {
            $success = "";
            $m(".success-msg").css("display", "none");
        });

        $m(".test").click(function () {
            $m(".mo2f_thumbnail").hide();
            $m("#twofactorselect").show();
            $m("#test_2factor").val($m(this).data("method"));
            $m("#mo2f_2factor_test_form").submit();
        });
    });
    function setactive($id) {
        $m(".navbar-tabs>li").removeClass("active");
        $m("#minisupport").show();
        $id = '#' + $id;
        $m($id).addClass("active");
    }
    function voiddisplay($href) {
        $m(".page").css("display", "none");
        $m($href).css("display", "block");
    }
    function mo2f_valid(f) {
        !(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(/[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
    }
    jQuery(document).ready(function () {

        var tempHorSize = '<?php echo $iconCustomSize ?>';
        var tempHorTheme = '<?php echo $loginTheme ?>';
        var tempHorCustomTheme = '<?php echo $loginCustomTheme ?>';
        var tempHorCustomColor = '<?php echo $iconCustomColor ?>';
        var tempHorSpace = '<?php echo $iconSpace ?>';
        var tempHorHeight = '<?php echo $iconCustomHeight ?>';
        gluuOxLoginPreview(setSizeOfIcons(), tempHorTheme, tempHorCustomTheme, tempHorCustomColor, tempHorSpace, tempHorHeight);
        checkLoginButton();

    });
    function setLoginTheme() {
        return jQuery('input[name=gluuoxd_openid_login_theme]:checked', '#form-apps').val();
    }
    function setLoginCustomTheme() {
        return jQuery('input[name=gluuoxd_openid_login_custom_theme]:checked', '#form-apps').val();
    }
    function setSizeOfIcons() {
        if ((jQuery('input[name=gluuoxd_openid_login_theme]:checked', '#form-apps').val()) == 'longbutton') {
            return document.getElementById('gluuox_login_icon_width').value;
        } else {
            return document.getElementById('gluuox_login_icon_size').value;
        }
    }
    function gluuOxLoginPreview(t, r, l, p, n, h) {

        if (l == 'default') {
            if (r == 'longbutton') {
                var a = "btn-defaulttheme";
                jQuery("." + a).css("width", t + "px");
                if (h > 26) {
                    jQuery("." + a).css("height", "26px");
                    jQuery("." + a).css("padding-top", (h - 26) / 2 + "px");
                    jQuery("." + a).css("padding-bottom", (h - 26) / 2 + "px");
                } else {
                    jQuery("." + a).css("height", h + "px");
                    jQuery("." + a).css("padding-top", (h - 26) / 2 + "px");
                    jQuery("." + a).css("padding-bottom", (h - 26) / 2 + "px");
                }
                jQuery(".fa").css("padding-top", (h - 35) + "px");
                jQuery("." + a).css("margin-bottom", n + "px");
            } else {
                var a = "gluuox_login_icon_preview";
                jQuery("." + a).css("margin-left", n + "px");
                if (r == "circle") {
                    jQuery("." + a).css({height: t, width: t});
                    jQuery("." + a).css("borderRadius", "999px");
                } else if (r == "oval") {
                    jQuery("." + a).css("borderRadius", "5px");
                    jQuery("." + a).css({height: t, width: t});
                } else if (r == "square") {
                    jQuery("." + a).css("borderRadius", "0px");
                    jQuery("." + a).css({height: t, width: t});
                }
            }
        }
        else if (l == 'custom') {
            if (r == 'longbutton') {
                var a = "btn-customtheme";
                jQuery("." + a).css("width", (t) + "px");
                if (h > 26) {
                    jQuery("." + a).css("height", "26px");
                    jQuery("." + a).css("padding-top", (h - 26) / 2 + "px");
                    jQuery("." + a).css("padding-bottom", (h - 26) / 2 + "px");
                } else {
                    jQuery("." + a).css("height", h + "px");
                    jQuery("." + a).css("padding-top", (h - 26) / 2 + "px");
                    jQuery("." + a).css("padding-bottom", (h - 26) / 2 + "px");
                }
                jQuery("." + a).css("margin-bottom", n + "px");
                jQuery("." + a).css("background", p);
            } else {
                var a = "gluuOx_custom_login_icon_preview";
                jQuery("." + a).css({height: t - 8, width: t});
                jQuery("." + a).css("padding-top", "8px");
                jQuery("." + a).css("margin-left", n + "px");
                jQuery("." + a).css("background", p);

                if (r == "circle") {
                    jQuery("." + a).css("borderRadius", "999px");
                } else if (r == "oval") {
                    jQuery("." + a).css("borderRadius", "5px");
                } else if (r == "square") {
                    jQuery("." + a).css("borderRadius", "0px");
                }
                jQuery("." + a).css("font-size", (t - 16) + "px");
            }
        }
        previewLoginIcons();
    }
    function checkLoginButton() {
        if (document.getElementById('iconwithtext').checked) {
            if (setLoginCustomTheme() == 'default') {
                jQuery(".gluuox_login_icon_preview").hide();
                jQuery(".gluuOx_custom_login_icon_preview").hide();
                jQuery(".btn-customtheme").hide();
                jQuery(".btn-defaulttheme").show();
            } else if (setLoginCustomTheme() == 'custom') {
                jQuery(".gluuox_login_icon_preview").hide();
                jQuery(".gluuOx_custom_login_icon_preview").hide();
                jQuery(".btn-defaulttheme").hide();
                jQuery(".btn-customtheme").show();
            }
            jQuery("#commontheme").hide();
            jQuery(".longbuttontheme").show();
        }
        else {
            if (setLoginCustomTheme() == 'default') {
                jQuery(".gluuox_login_icon_preview").show();
                jQuery(".btn-defaulttheme").hide();
                jQuery(".btn-customtheme").hide();
                jQuery(".gluuOx_custom_login_icon_preview").hide();
            } else if (setLoginCustomTheme() == 'custom') {
                jQuery(".gluuox_login_icon_preview").hide();
                jQuery(".gluuOx_custom_login_icon_preview").show();
                jQuery(".btn-defaulttheme").hide();
                jQuery(".btn-customtheme").hide();
            }
            jQuery("#commontheme").show();
            jQuery(".longbuttontheme").hide();
        }

        previewLoginIcons();
    }
    function previewLoginIcons() {
        var flag = 0;
        <?php foreach($custom_scripts as $custom_script):?>
        if (document.getElementById('<?php echo $custom_script['value'];?>_enable').checked) {
            flag = 1;
            if (document.getElementById('gluuoxd_openid_login_default_radio').checked && !document.getElementById('iconwithtext').checked)
                jQuery("#gluuox_login_icon_preview_<?php echo $custom_script['value'];?>").show();
            if (document.getElementById('gluuoxd_openid_login_custom_radio').checked && !document.getElementById('iconwithtext').checked)
                jQuery("#gluuOx_custom_login_icon_preview_<?php echo $custom_script['value'];?>").show();
            if (document.getElementById('gluuoxd_openid_login_default_radio').checked && document.getElementById('iconwithtext').checked)
                jQuery("#gluuox_login_button_preview_<?php echo $custom_script['value'];?>").show();
            if (document.getElementById('gluuoxd_openid_login_custom_radio').checked && document.getElementById('iconwithtext').checked)
                jQuery("#gluuOx_custom_login_button_preview_<?php echo $custom_script['value'];?>").show();
        }
        else if (!document.getElementById('<?php echo $custom_script['value'];?>_enable').checked) {
            jQuery("#gluuox_login_icon_preview_<?php echo $custom_script['value'];?>").hide();
            jQuery("#gluuOx_custom_login_icon_preview_<?php echo $custom_script['value'];?>").hide();
            jQuery("#gluuox_login_button_preview_<?php echo $custom_script['value'];?>").hide();
            jQuery("#gluuOx_custom_login_button_preview_<?php echo $custom_script['value'];?>").hide();
        }
        <?php endforeach;?>
        if (flag) {
            jQuery("#no_apps_text").hide();
        } else {
            jQuery("#no_apps_text").show();
        }



    }
    var selectedApps = [];
    function setTheme() {
        return jQuery('input[name=gluuoxd_openid_share_theme]:checked', '#settings_form').val();
    }
    function setCustomTheme() {
        return jQuery('input[name=gluuoxd_openid_share_custom_theme]:checked', '#settings_form').val();
    }
    function gluuOxLoginSizeValidate(e) {
        var t = parseInt(e.value.trim());
        t > 60 ? e.value = 60 : 20 > t && (e.value = 20);
        reloadLoginPreview();
    }
    function gluuOxLoginSpaceValidate(e) {
        var t = parseInt(e.value.trim());
        t > 60 ? e.value = 60 : 0 > t && (e.value = 0);
        reloadLoginPreview();
    }
    function gluuOxLoginWidthValidate(e) {
        var t = parseInt(e.value.trim());
        t > 1000 ? e.value = 1000 : 140 > t && (e.value = 140)
        reloadLoginPreview();
    }
    function gluuOxLoginHeightValidate(e) {
        var t = parseInt(e.value.trim());
        t > 100 ? e.value = 100 : 10 > t && (e.value = 10)
        reloadLoginPreview();
    }
    function reloadLoginPreview() {
        if (setLoginTheme() == 'longbutton')
            gluuOxLoginPreview(document.getElementById('gluuox_login_icon_width').value, setLoginTheme(), setLoginCustomTheme(), document.getElementById('gluuox_login_icon_custom_color').value, document.getElementById('gluuox_login_icon_space').value,
                document.getElementById('gluuox_login_icon_height').value);
        else
            gluuOxLoginPreview(document.getElementById('gluuox_login_icon_size').value, setLoginTheme(), setLoginCustomTheme(), document.getElementById('gluuox_login_icon_custom_color').value, document.getElementById('gluuox_login_icon_space').value);
    }
</script>
<div class="heading"><h3>GLUU SSO 2.4.2 </h3></div>

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
            <li id="account_setup"><a data-method="#accountsetup">General</a></li>
            <li id="social-sharing-setup"><a data-method="#socialsharing">OpenID Connect Configuration</a></li>
            <li id="social-login-setup"><a data-method="#sociallogin">SuiteCRM Configuration</a></li>
            <li id="help_trouble"><a data-method="#helptrouble">Help & Troubleshooting</a></li>
        </ul>

        <div class="container-page">
            <!-- General -->
            <?php if (!$oxd_id) { ?>
                <!-- General tab-->
                <div class="page" id="accountsetup">
                    <div class="mo2f_table_layout">
                        <form id="register_GluuOxd" name="f" method="post"
                              action="index.php?module=Gluussos&action=gluuPostData">
                            <input type="hidden" name="form_key" value="general_register_page"/>
                            <div class="login_GluuOxd">
                                <div class="mess_red">
                                    Please enter the details of your OpenID Connect Provider.
                                </div>
                                <br/>
                                <div><h3>Register your site with an OpenID Connect Provider</h3></div>
                                <hr>
                                <div class="mess_red">If you do not have an OpenID Connect provider, you may want to look at the Gluu Server (
                                    <a target="_blank" href="http://www.gluu.org/docs">Like SuiteCRM, there is a free open source Community Edition. For more information about Gluu Server support please visit <a target="_blank" href="http://www.gluu.org">our website.</a></a>)
                                </div>
                                <div class="mess_red">
                                    <h3>Instructions to Install oxd server</h3>
                                    <br><b>NOTE:</b> The oxd server should be installed on the same server as your SuiteCRM site. It is recommended that the oxd server listen only on the localhost interface, so only your local applications can reach its API's.
                                    <ol style="list-style:decimal !important; margin: 30px">
                                        <li>Extract and copy in your DMZ Server.</li>
                                        <li>Download the latest oxd-server package for Centos or Ubuntu. See
                                            <a target="_blank" href="http://gluu.org/docs-oxd">oxd docs</a> for more info.
                                        </li><li>If you are installing an .rpm or .deb, make sure you have Java in your server.
                                        </li><li>Edit <b>oxd-conf.json</b> in the <b>conf</b> directory to specify the port on which
                                            it will run, and specify the hostname of the OpenID Connect provider.</li>
                                        <li>Open the command line and navigate to the extracted folder in the <b>bin</b> directory.</li>
                                        <li>For Linux environment, run <b>sh oxd-start.sh &amp;</b></li>
                                        <li>For Windows environment, run <b>oxd-start.bat</b></li>
                                        <li>After the server starts, set the port number and your email in this page and click Next.</li>
                                    </ol>
                                </div>
                                <hr>
                                <div>
                                    <table class="table">
                                        <tr>
                                            <td><b><font color="#FF0000">*</font>Admin Email:</b></td>
                                            <td><input class="" type="email" name="loginemail" id="loginemail"
                                                       autofocus="true" required placeholder="person@example.com"
                                                       style="width:400px;"
                                                       value="<?php echo $oxd_config['admin_email']; ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b><font color="#FF0000">*</font>Port number:</b></td>
                                            <td>
                                                <input class="" type="number" name="oxd_port" min="0" max="65535"
                                                       value="<?php echo $oxd_config['oxd_host_port']; ?>"
                                                       style="width:400px;" placeholder="Enter port number."/>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <br/>
                                <div><input type="submit" name="submit" value="Next" style="width: 120px" class=""/></div>
                                <br/>
                                <br/>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } else{?>
                <div class="page" id="accountsetup">
                    <div>
                        <div>
                            <div class="about">
                                <h3 style="color: #45a8ff" class="sc"><img style=" height: 45px; margin-left: 20px;" src="modules/Gluussos/GluuOxd_Openid/images/icons/ox.png"/>&nbsp; server config</h3>
                            </div>
                        </div>
                        <div class="entry-edit" >
                            <div class="entry-edit-head">
                                <h4 class="icon-head head-edit-form fieldset-legend">OXD id</h4>
                            </div>
                            <div class="fieldset">
                                <div class="hor-scroll">
                                    <table class="form-list container">
                                        <tr class="wrapper-trr">
                                            <td class="value">
                                                <input style="width: 500px !important;" type="text" name="oxd_id" value="<?php echo $oxd_id; ?>" <?php echo 'disabled' ?>/>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form action="index.php?module=Gluussos&action=gluuPostData" method="post">
                        <input type="hidden" name="form_key" value="general_oxd_id_reset"/>
                        <p><input style="width: 200px; background-color: red !important; cursor: pointer" type="submit" class="button button-primary " value="Reset configurations" name="resetButton"/></p>
                    </form>
                </div>
            <?php }?>
            <!--Scopes and custom scripts tab-->
            <div class="page" id="socialsharing">
                <?php if (!$oxd_id){ ?>
                    <div class="mess_red">
                        Please enter OXD configuration to continue.
                    </div><br/>
                <?php } ?>
                <div>
                    <form action="index.php?module=Gluussos&action=gluuPostData" method="post"
                          enctype="multipart/form-data">
                        <input type="hidden" name="form_key" value="openid_config_page"/>
                        <div>
                            <div>
                                <div class="about">
                                    <br/>
                                    <h3 style="color: #00aa00" class="sc"><img style="height: 45px; margin-left: 30px;" src="modules/Gluussos/GluuOxd_Openid/images/icons/gl.png"/> &nbsp; server config
                                    </h3>
                                </div>
                            </div>
                            <div class="entry-edit" >
                                <div class="entry-edit-head" style="background-color: #00aa00 !important;">
                                    <h4 class="icon-head head-edit-form fieldset-legend">All Scopes</h4>
                                </div>
                                <div class="fieldset">
                                    <div class="hor-scroll">
                                        <table class="form-list">
                                            <tr class="wrapper-trr">
                                                <?php foreach ($get_scopes as $scop) : ?>
                                                    <td class="value">
                                                        <?php if ($scop == 'openid'){?>
                                                            <input <?php if (!$oxd_id) echo ' disabled ' ?> type="hidden"  name="scope[]"  <?php if ($oxd_config && in_array($scop, $oxd_config['scope'])) {
                                                                echo " checked "; } ?> value="<?php echo $scop; ?>" />
                                                        <?php } ?>
                                                        <input <?php if (!$oxd_id) echo ' disabled ' ?> type="checkbox"  name="scope[]"  <?php if ($oxd_config && in_array($scop, $oxd_config['scope'])) {
                                                            echo " checked "; } ?> id="<?php echo $scop; ?>" value="<?php echo $scop; ?>" <?php if ($scop == 'openid') echo ' disabled '; ?> />
                                                        <label for="<?php echo $scop; ?>"><?php echo $scop; ?></label>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        </table>
                                        <table class="form-list" style="text-align: center">
                                            <tr class="wrapper-tr" style="text-align: center">
                                                <th style="border: 1px solid #43ffdf; width: 70px;text-align: center"><h3>N</h3></th>
                                                <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"><h3>Name</h3></th>
                                                <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"><h3>Delete</h3></th>
                                            </tr>
                                            <?php
                                            $n = 0;
                                            foreach ($get_scopes as $scop) {
                                                $n++;
                                                ?>
                                                <tr class="wrapper-trr">
                                                    <td style="border: 1px solid #43ffdf; padding: 0px; width: 70px"><h3><?php echo $n; ?></h3></td>
                                                    <td style="border: 1px solid #43ffdf; padding: 0px; width: 200px"><h3><label for="<?php echo $scop; ?>"><?php echo $scop; ?></label></h3></td>
                                                    <td style="border: 1px solid #43ffdf; padding: 0px; width: 200px">
                                                        <?php if ($n == 1): ?>
                                                            <form></form>
                                                        <?php endif; ?>
                                                        <form
                                                            action="index.php?module=Gluussos&action=gluuPostData"
                                                            method="post">
                                                            <input type="hidden" name="form_key"
                                                                   value="openid_config_delete_scop"/>
                                                            <input type="hidden"
                                                                   value="<?php echo $scop; ?>"
                                                                   name="value_scope"/>
                                                            <?php if ($scop != 'openid'){ ?>
                                                                <input  style="width: 100px; background-color: red !important; cursor: pointer"
                                                                        type="submit"
                                                                        class="button button-primary " <?php if (!$oxd_id) echo 'disabled' ?>
                                                                        value="Delete" name="delete_scop"/>
                                                            <?php } ?>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="entry-edit" style="display: none">
                                <div class="entry-edit-head" style="background-color: #00aa00 !important;">
                                    <h4 class="icon-head head-edit-form fieldset-legend">Add scopes</h4>
                                </div>
                                <div class="fieldset">
                                    <input type="button" id="adding" class="button button-primary button-large add" style="width: 100px" value="Add scopes"/>
                                    <div class="hor-scroll">
                                        <table class="form-list5 container">
                                            <tr class="wrapper-tr">
                                                <td class="value">
                                                    <input type="text" placeholder="Input scope name" name="scope_name[]"/>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="entry-edit" >
                                <div class="entry-edit-head" style="background-color: #00aa00 !important;">
                                    <h4 class="icon-head head-edit-form fieldset-legend">All custom scripts</h4>
                                </div>
                                <div class="fieldset">
                                    <div class="hor-scroll">
                                        <h3>Manage Authentication</h3>
                                        <p>An OpenID Connect Provider (OP) like the Gluu Server may provide many different work flows for
                                            authentication. For example, an OP may offer password authentication, token authentication, social
                                            authentication, biometric authentication, and other different mechanisms. Offering a lot of different
                                            types of authentication enables an OP to offer the most convenient, secure, and affordable option to
                                            identify a person, depending on the need to mitigate risk, and the sensors and inputs available on the
                                            device that the person is using.
                                        </p>
                                        <p>
                                            The OP enables a client (like a SuiteCRM site), to signal which type of authentication should be
                                            used. The client can register a
                                            <a target="_blank" href="http://openid.net/specs/openid-connect-registration-1_0.html#ClientMetadata">default_acr_value</a>
                                            or during the authentication process, a client may request a specific type of authentication using the
                                            <a target="_blank" href="http://openid.net/specs/openid-connect-core-1_0.html#AuthRequest">acr_values</a> parameter.
                                            This is the mechanism that the Gluu SSO module uses: each login icon corresponds to a acr request value.
                                            For example, and acr may tell the OpenID Connect to use Facebook, Google or even plain old password authentication.
                                            The nice thing about this approach is that your applications (like SuiteCRM) don't have
                                            to implement the business logic for social login--it's handled by the OpenID Connect Provider.
                                        </p>
                                        <p>
                                            If you are using the Gluu Server as your OP, you'll notice that in the Manage Custom Scripts
                                            tab of oxTrust (the Gluu Server admin interface), each authentication script has a name.
                                            This name corresponds to the acr value.  The default acr for password authentication is set in
                                            the
                                            <a target="_blank" href="https://www.gluu.org/docs/admin-guide/configuration/#manage-authentication">LDAP Authentication</a>,
                                            section--look for the "Name" field. Likewise, each custom script has a "Name", for example see the
                                            <a target="_blank" href="https://www.gluu.org/docs/admin-guide/configuration/#manage-custom-scripts">Manage Custom Scripts</a> section.
                                        </p>
                                        <table style="width:100%;display: table;">
                                            <tbody>
                                            <tr>
                                                <?php
                                                foreach ($custom_scripts as $custom_script) {
                                                    ?>
                                                    <td style="width:25%">
                                                        <input type="checkbox" <?php if (!$oxd_id) echo 'disabled'; ?>
                                                               id="<?php echo $custom_script['value']; ?>_enable"
                                                               class="app_enable"
                                                               name="gluuoxd_openid_<?php echo $custom_script['value']; ?>_enable"
                                                               value="1"
                                                               onchange="previewLoginIcons();" <?php if ($db->fetchRow($db->query("SELECT `gluu_value` FROM `gluu_table` WHERE `gluu_action` LIKE '".$custom_script['value']."Enable'"))['gluu_value']) echo "checked"; ?> /><b><?php echo $custom_script['name']; ?></b>
                                                    </td>
                                                    <?php
                                                }
                                                ?>
                                            </tr>
                                            <tr style="display: none;">
                                            </tr>
                                            </tbody>
                                        </table>
                                        <table class="form-list" style="text-align: center">
                                            <tr class="wrapper-tr" style="text-align: center">
                                                <th style="border: 1px solid #43ffdf; width: 70px;text-align: center"><h3>N</h3></th>
                                                <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"><h3>Display Name</h3></th>
                                                <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"><h3>ACR Value</h3></th>
                                                <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"><h3>Image</h3></th>
                                                <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"><h3>Delete</h3></th>
                                            </tr>
                                            <?php
                                            $n = 0;
                                            foreach ($custom_scripts as $custom_script) {
                                                $n++;
                                                ?>
                                                <tr class="wrapper-trr">
                                                    <td style="border: 1px solid #43ffdf; padding: 0px; width: 70px"><h3><?php echo $n; ?></h3></td>
                                                    <td style="border: 1px solid #43ffdf; padding: 0px; width: 200px"><h3><?php echo $custom_script['name']; ?></h3></td>
                                                    <td style="border: 1px solid #43ffdf; padding: 0px; width: 200px"><h3><?php echo $custom_script['value']; ?></h3></td>
                                                    <td style="border: 1px solid #43ffdf; padding: 0px; width: 200px"><img src="<?php echo $custom_script['image']; ?>" width="40px" height="40px"/></td>
                                                    <td style="border: 1px solid #43ffdf; padding: 0px; width: 200px">
                                                        <?php if ($n == 1): ?>
                                                            <form></form>
                                                        <?php endif; ?>
                                                        <form
                                                            action="index.php?module=Gluussos&action=gluuPostData"
                                                            method="post">
                                                            <input type="hidden" name="form_key"
                                                                   value="openid_config_delete_custom_scripts"/>
                                                            <input type="hidden"
                                                                   value="<?php echo $custom_script['value']; ?>"
                                                                   name="value_script"/>
                                                            <input
                                                                style="width: 100px; background-color: red !important; cursor: pointer"
                                                                type="submit"
                                                                class="button button-primary " <?php if (!$oxd_id) echo 'disabled' ?>
                                                                value="Delete" name="delete_config"/>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </table>
                                    </div>
                                </div>
                                <br/>
                                <div class="entry-edit-head" style="background-color: #00aa00 !important;">
                                    <h4 class="icon-head head-edit-form fieldset-legend">Add multiple custom scripts</h4>
                                    <br/>
                                    <p style="color:#cc0b07; font-style: italic; font-weight: bold;font-size: larger"> Both fields are required</p>
                                </div>
                                <div class="fieldset">
                                    <div class="hor-scroll">
                                        <input type="hidden" name="count_scripts" value="1" id="count_scripts">
                                        <input type="button" class="button button-primary button-large " style="width: 100px" id="adder" value="Add acr"/>
                                        <table class="form-list1 container">
                                            <tr class="count_scripts wrapper-trr">
                                                <td class="value">
                                                    <input style='width: 200px !important;' type="text" placeholder="Display name (example Google+)" name="name_in_site_1"/>
                                                </td>
                                                <td class="value">
                                                    <input style='width: 270px !important;' type="text" placeholder="ACR Value (script name in the Gluu Server)" name="name_in_gluu_1"/>
                                                </td>
                                                <td class="value">
                                                    <input type="file" accept="image/png" name="images_1"/>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <input class="set_oxd_config" style="width: 100px" type="submit" class="button button-primary button-large" <?php if (!$oxd_id) echo 'disabled' ?> value="Save" name="set_oxd_config"/>
                            <br/>
                        </div>
                    </form>
                </div>
            </div>
            <!--Gluu and social login config tab-->
            <div class="page" id="sociallogin">
                <?php if (!$oxd_id){ ?>
                    <div class="mess_red">
                        Please enter OXD configuration to continue.
                    </div><br/>
                <?php } ?>

                <form id="form-apps" name="form-apps" method="post"
                      action="index.php?module=Gluussos&action=gluuPostData" enctype="multipart/form-data">
                    <input type="hidden" name="form_key" value="sugar_crm_config_page"/>
                    <div class="mo2f_table_layout">
                        <input <?php if (!$oxd_id) echo 'disabled'; ?> type="submit" name="submit" value="Save" style="width:100px;margin-right:2%" class="button button-primary button-large">
                    </div>
                    <div id="twofactor_list" class="mo2f_table_layout">
                        <h3>Gluu login config </h3>
                        <hr>
                        <p style="font-size:14px">Customize your login icons using a range of shapes and sizes. You can choose different places to display these icons and also customize redirect url after login.</p>
                        <br/>
                        <hr>
                        <br>
                        <h3>Customize Login Icons</h3>
                        <p>Customize shape, theme and size of the login icons</p>
                        <table style="width:100%;display: table;">
                            <tbody>
                            <tr>
                                <td>
                                    <b>Shape</b>
                                    <b style="margin-left:130px; display: none">Theme</b>
                                    <b style="margin-left:130px;">Space between Icons</b>
                                    <b style="margin-left:86px;">Size of Icons</b>
                                </td>
                            </tr>
                            <tr>
                                <td class="gluuoxd_openid_table_td_checkbox">
                                    <input type="radio" <?php if (!$oxd_id) echo 'disabled'; ?>
                                           name="gluuoxd_openid_login_theme" value="circle"
                                           onclick="checkLoginButton();gluuOxLoginPreview(document.getElementById('gluuox_login_icon_size').value ,'circle',setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value)"
                                           style="width: auto;" checked>Round
                            <span style="margin-left:106px; display: none">
                                <input type="radio" <?php if (!$oxd_id) echo 'disabled'; ?>
                                       id="gluuoxd_openid_login_default_radio" name="gluuoxd_openid_login_custom_theme"
                                       value="default"
                                       onclick="checkLoginButton();gluuOxLoginPreview(setSizeOfIcons(), setLoginTheme(),'default',document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value,document.getElementById('gluuox_login_icon_height').value)"
                                       checked>Default
                            </span>
                            <span style="margin-left:111px;">
                                    <input
                                        style="width:50px" <?php if (!$oxd_id) echo ' disabled '; ?>
                                           onkeyup="gluuOxLoginSpaceValidate(this)" id="gluuox_login_icon_space"
                                           name="gluuox_login_icon_space" type="text" value="<?php echo $iconSpace; ?>" />
                                    <input
                                        id="gluuox_login_space_plus" <?php if (!$oxd_id) echo 'disabled'; ?> <?php if (!$oxd_id) echo 'disabled'; ?> <?php if (!$oxd_id) echo 'disabled'; ?>
                                        type="button" value="+"
                                        onmouseup="document.getElementById('gluuox_login_icon_space').value=parseInt(document.getElementById('gluuox_login_icon_space').value)+1;gluuOxLoginPreview(setSizeOfIcons() ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value)">
                                    <input
                                        id="gluuox_login_space_minus" <?php if (!$oxd_id) echo 'disabled'; ?> <?php if (!$oxd_id) echo 'disabled'; ?>
                                        type="button" value="-"
                                        onmouseup="document.getElementById('gluuox_login_icon_space').value=parseInt(document.getElementById('gluuox_login_icon_space').value)-1;gluuOxLoginPreview(setSizeOfIcons()  ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value)">
                            </span>
                            <span id="commontheme" style="margin-left:95px">
                                <input style="width:50px " <?php if (!$oxd_id) echo 'disabled'; ?> id="gluuox_login_icon_size"
                                       onkeyup="gluuOxLoginSizeValidate(this)" name="gluuox_login_icon_custom_size" type="text"
                                       value="<?php if ($iconCustomSize) echo $iconCustomSize; else echo '35'; ?>">
                                <input id="gluuox_login_size_plus" <?php if (!$oxd_id) echo 'disabled'; ?> type="button" value="+"
                                       onmouseup="document.getElementById('gluuox_login_icon_size').value=parseInt(document.getElementById('gluuox_login_icon_size').value)+1;gluuOxLoginPreview(document.getElementById('gluuox_login_icon_size').value ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value)">
                                <input id="gluuox_login_size_minus" <?php if (!$oxd_id) echo 'disabled'; ?> type="button" value="-"
                                       onmouseup="document.getElementById('gluuox_login_icon_size').value=parseInt(document.getElementById('gluuox_login_icon_size').value)-1;gluuOxLoginPreview(document.getElementById('gluuox_login_icon_size').value ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value)">
                            </span>
                            <span style="margin-left: 95px; display: none;" class="longbuttontheme">Width:&nbsp;
                                <input style="width:50px" <?php if (!$oxd_id) echo 'disabled'; ?> id="gluuox_login_icon_width"
                                       onkeyup="gluuOxLoginWidthValidate(this)" name="gluuox_login_icon_custom_width" type="text"
                                       value="<?php echo $iconCustomWidth; ?>">
                                <input id="gluuox_login_width_plus" <?php if (!$oxd_id) echo 'disabled'; ?> type="button" value="+"
                                       onmouseup="document.getElementById('gluuox_login_icon_width').value=parseInt(document.getElementById('gluuox_login_icon_width').value)+1;gluuOxLoginPreview(document.getElementById('gluuox_login_icon_width').value ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value,document.getElementById('gluuox_login_icon_height').value)">
                                <input id="gluuox_login_width_minus" <?php if (!$oxd_id) echo 'disabled'; ?> type="button" value="-"
                                       onmouseup="document.getElementById('gluuox_login_icon_width').value=parseInt(document.getElementById('gluuox_login_icon_width').value)-1;gluuOxLoginPreview(document.getElementById('gluuox_login_icon_width').value ,setLoginTheme(),setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value,document.getElementById('gluuox_login_icon_height').value)">
                            </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="gluuoxd_openid_table_td_checkbox">
                                    <input type="radio"
                                           name="gluuoxd_openid_login_theme" <?php if (!$oxd_id) echo 'disabled'; ?>
                                           value="oval"
                                           onclick="checkLoginButton();gluuOxLoginPreview(document.getElementById('gluuox_login_icon_size').value,'oval',setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value,document.getElementById('gluuox_login_icon_size').value )"
                                           style="width: auto;" <?php if ($loginTheme == 'oval') echo "checked"; ?>>Rounded Edges
                        <span style="margin-left:50px; display: none">
                                <input type="radio"
                                       <?php if (!$oxd_id) echo 'disabled'; ?>id="gluuoxd_openid_login_custom_radio"
                                       name="gluuoxd_openid_login_custom_theme" value="custom"
                                       onclick="checkLoginButton();gluuOxLoginPreview(setSizeOfIcons(), setLoginTheme(),'custom',document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value,document.getElementById('gluuox_login_icon_height').value)"
                                    <?php if ($loginCustomTheme == 'custom') echo "checked"; ?> >Custom Background*
                                </span>
                            <span style="margin-left: 256px; display: none;" class="longbuttontheme">Height:
                            <input style="width:50px" <?php if (!$oxd_id) echo 'disabled'; ?> id="gluuox_login_icon_height"
                                   onkeyup="gluuOxLoginHeightValidate(this)" name="gluuox_login_icon_custom_height" type="text"
                                   value="<?php if ($iconCustomHeight) echo $iconCustomHeight; else echo '35'; ?>">
                            <input id="gluuox_login_height_plus" <?php if (!$oxd_id) echo 'disabled'; ?> type="button" value="+"
                                   onmouseup="document.getElementById('gluuox_login_icon_height').value=parseInt(document.getElementById('gluuox_login_icon_height').value)+1;gluuOxLoginPreview(document.getElementById('gluuox_login_icon_width').value,setLoginTheme(),setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value,document.getElementById('gluuox_login_icon_height').value)">
                            <input id="gluuox_login_height_minus" <?php if (!$oxd_id) echo 'disabled'; ?> type="button" value="-"
                               onmouseup="document.getElementById('gluuox_login_icon_height').value=parseInt(document.getElementById('gluuox_login_icon_height').value)-1;gluuOxLoginPreview(document.getElementById('gluuox_login_icon_width').value,setLoginTheme(),setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value,document.getElementById('gluuox_login_icon_height').value)">
                        </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="gluuoxd_openid_table_td_checkbox">
                                    <input type="radio" <?php if (!$oxd_id) echo 'disabled'; ?>
                                           name="gluuoxd_openid_login_theme" value="square"
                                           onclick="checkLoginButton();gluuOxLoginPreview(document.getElementById('gluuox_login_icon_size').value ,'square',setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value,document.getElementById('gluuox_login_icon_size').value )"
                                           style="width: auto;" <?php if ($loginTheme == 'square') echo "checked"; ?>>Square
                                    <span style="margin-left:113px; display: none">
                                        <input type="color" <?php if (!$oxd_id) echo 'disabled'; ?>
                                               name="gluuox_login_icon_custom_color" id="gluuox_login_icon_custom_color"
                                               value="<?php echo $iconCustomColor; ?>"
                                               onchange="gluuOxLoginPreview(setSizeOfIcons(), setLoginTheme(),'custom',document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value)">
                                    </span>
                                </td>
                            </tr>
                            <tr style="display: none">
                                <td class="gluuoxd_openid_table_td_checkbox">
                                    <input
                                        type="radio" <?php if (!$oxd_id) echo 'disabled'; ?> <?php if (!$oxd_id) echo 'disabled'; ?>
                                        id="iconwithtext" name="gluuoxd_openid_login_theme" value="longbutton"
                                        onclick="checkLoginButton();gluuOxLoginPreview(document.getElementById('gluuox_login_icon_width').value ,'longbutton',setLoginCustomTheme(),document.getElementById('gluuox_login_icon_custom_color').value,document.getElementById('gluuox_login_icon_space').value,document.getElementById('gluuox_login_icon_height').value)"
                                        style="width: auto;" <?php if ($loginTheme == 'longbutton') echo "checked"; ?>>Long
                                    Button with Text
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <br>
                        <h3>Preview : </h3>
                        <span hidden id="no_apps_text">No apps selected</span>
                        <div>
                            <?php foreach ($custom_scripts as $custom_script): ?>
                                <img class="gluuox_login_icon_preview"
                                     id="gluuox_login_icon_preview_<?php echo $custom_script['value']; ?>"
                                     src="<?php echo $custom_script['image']; ?>"/>
                            <?php endforeach; ?>
                        </div>
                        <div>
                            <?php foreach ($custom_scripts as $custom_script): ?>
                                <a id="gluuox_login_button_preview_<?php echo $custom_script['value']; ?>"
                                   class="btn btn-block btn-defaulttheme btn-social btn-<?php echo $custom_script['value']; ?> btn-custom-size"
                                   style="width: <?php echo $iconCustomWidth; ?>px; height:<?php echo $iconCustomHeight; ?>px; padding-top: 6px; padding-bottom: 6px; margin-bottom: <?php echo $iconSpace . 'px'; ?>">
                                    <i class="fa fa-<?php echo $custom_script['value']; ?>-plus"
                                       style="padding-top: 0px;"></i>Login with <?php echo $custom_script['name']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <div>
                            <?php foreach ($custom_scripts as $custom_script): ?>
                                <i class="gluuOx_custom_login_icon_preview fa fa-<?php echo $custom_script['value']; ?>-plus"
                                   id="gluuOx_custom_login_icon_preview_<?php echo $custom_script['value']; ?>"
                                   style="color:#ffffff;text-align:center;margin-top:5px;"></i>
                            <?php endforeach; ?>
                        </div>
                        <div>
                            <?php foreach ($custom_scripts as $custom_script): ?>
                                <a id="gluuOx_custom_login_button_preview_<?php echo $custom_script['value']; ?>"
                                   class="btn btn-block btn-customtheme btn-social   btn-custom-size"
                                   style="width:<?php echo $iconCustomWidth; ?>px;height:<?php echo $iconCustomHeight; ?>px;padding-top: 6px;padding-bottom: 6px;margin-bottom:<?php echo $iconSpace; ?>px;background:<?php echo $iconCustomColor; ?>;">
                                    <i class="fa fa-<?php echo $custom_script['value']; ?>-plus"></i>Login
                                    with <?php echo $custom_script['name']; ?></a>
                            <?php endforeach; ?>
                        </div>
                        <br><br>
                    </div>
                </form>
            </div>

            <!-- Help & Troubleshooting tab-->
            <div class="page" id="helptrouble">
                <h1>SuiteCRM GLUU SSO module </h1>
                <p><img alt="image" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/plugin.jpg" /></p>
                <p>SuiteCRM-GLUU-SSO module gives access for login to your SuiteCRM site, with the help of GLUU server.</p>
                <p>There are already 2 versions of SUITECRM-GLUU-SSO (2.4.2 and 2.4.3) modules, each in its turn is working with oxD and GLUU servers.
                    For example if you are using SUITECRM-gluu-sso-2.4.2 module, you need to connect with oxD-server-2.4.2.</p>
                <p>Now I want to explain in details how to use module step by step. </p>
                <p>Module will not be working if your host does not have https://. </p>
                <h2>Step 1. Install Gluu-server</h2>
                <p>(version 2.4.2 or 2.4.3)</p>
                <p>If you want to use external gluu server, You can not do this step.   </p>
                <p><a href="https://www.gluu.org/docs/deployment/">Gluu-server installation gide</a>.</p>
                <h2>Step 2. Download oxD-server</h2>
                <p>(version 2.4.2 or 2.4.3)</p>
                <p><a href="https://ox.gluu.org/maven/org/xdi/oxd-server/2.4.2.Final/oxd-server-2.4.2.Final-distribution.zip">Download oxD-server-2.4.2.Final</a>.</p>
                <p>or</p>
                <p><a href="https://ox.gluu.org/maven/org/xdi/oxd-server/2.4.3-SNAPSHOT/oxd-server-2.4.3-SNAPSHOT-distribution.zip">Download oxD-server-2.4.3.DEMO</a>.</p>
                <h2>Step 3. Unzip and run oXD-server</h2>
                <ol>
                    <li>Unzip your oxD-server. </li>
                    <li>Open the command line and navigate to the extracted folder in the conf directory.</li>
                    <li>Open oxd-conf.json file.  </li>
                    <li>If your server is using 8099 port, please change "port" number to free port, which is not used.</li>
                    <li>Set parameter "op_host":"Your gluu-server-url (internal or external)"</li>
                    <li>Open the command line and navigate to the extracted folder in the bin directory.</li>
                    <li>For Linux environment, run sh oxd-start.sh&amp;. </li>
                    <li>For Windows environment, run oxd-start.bat.</li>
                    <li>After the server starts, go to Step 4.</li>
                </ol>
                <h2>Step 4. Download SuiteCRM-gluu-sso module</h2>
                <p>(version 2.4.2 or 2.4.3)</p>
                <p><a href="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/SuiteCRM_gluu_sso_2.4.2/SuiteCRM_gluu_sso_2.4.2.zip">Download SuiteCRM-gluu-sso-2.4.2 module</a>.</p>
                <p>or</p>
                <p><a href="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/SuiteCRM_gluu_sso_2.4.3/SuiteCRM_gluu_sso_2.4.3.zip">Download SuiteCRM-gluu-sso-2.4.3 module</a>.</p>
                <p>For example if you are using gluu-server-2.4.2 it is necessary to use oxD-server-2.4.2 and SuiteCRM-gluu-sso-2.4.2-module</p>
                <h2>Step 5. Install module</h2>
                <ol>
                    <li>
                        <p>Open menu tab Modules and click on <code>Install new module</code> button
                            <img alt="Manager" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/docu/d1.png" /> </p>
                    </li>
                    <li>Choose downloaded module and click on <code>INSTALL</code> button.
                        <img alt="upload" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/docu/d2.png" /> </li>
                </ol>
                <h2>Step 6. Activate module</h2>
                <ol>
                    <li>Go to Modules page</li>
                    <li>Find module Gluu SSO {version}, choose on enabled checkbox and save configuration.
                        <img alt="upload" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/docu/d3.png" /> </li>
                    <li>Go to Configuration page and open module configuration page.
                        <img alt="upload" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/docu/d4.png" /> </li>
                </ol>
                <h2>Step 7. General</h2>
                <p><img alt="General" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/docu/d5.png" />  </p>
                <ol>
                    <li>Admin Email: please add your or admin email address for registrating site in Gluu server.</li>
                    <li>Oxd port in your server: choose that port which is using oxd-server (see in oxd-server/conf/oxd-conf.json file).</li>
                    <li>Click next to continue.</li>
                </ol>
                <p>If You are successfully registered in gluu server, you will see bottom page.</p>
                <p><img alt="oxD_id" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/docu/d6.png" /></p>
                <p>For making sure go to your gluu server / OpenID Connect / Clients and search for your oxD ID</p>
                <p>If you want to reset configurations click on Reset configurations button.</p>
                <h2>Step 8. OpenID Connect Configuration</h2>
                <p>OpenID Connect Configuration page for SuiteCRM-gluu-sso 2.4.2 and SuiteCRM-gluu-sso 2.4.3 are different.</p>
                <h3>Scopes.</h3>
                <p>You can look all scopes in your gluu server / OpenID Connect / Scopes and understand the meaning of  every scope.
                    Scopes are need for getting loged in users information from gluu server.
                    Pay attention to that, which scopes you are using that are switched on in your gluu server.</p>
                <p>In SuiteCRM-gluu-sso 2.4.2  you can only enable, disable and delete scope.
                    <img alt="Scopes1" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/docu/d7.png" /> </p>
                <p>In SuiteCRM-gluu-sso 2.4.3 you can not only enable, disable and delete scope, but also add new scope, but when you add new scope by {any name}, necessary to add that scope in your gluu server too.
                    <img alt="Scopes2" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/docu/d8.png" /> </p>
                <h3>Custom scripts.</h3>
                <p><img alt="Customscripts" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/docu/d9.png" />  </p>
                <p>You can look all custom scripts in your gluu server / Configuration / Manage Custom Scripts / and enable login type, which type you want.
                    Custom Script represent itself the type of login, at this moment gluu server supports (U2F, Duo, Google +, Basic) types.</p>
                <h3>Pay attention to that.</h3>
                <ol>
                    <li>Which custom script you enable in your SuiteCRM site in order it must be switched on in gluu server too.</li>
                    <li>Which custom script you will be enable in OpenID Connect Configuration page, after saving that will be showed in SuiteCRM Configuration page too.</li>
                    <li>When you create new custom script, both fields are required.</li>
                </ol>
                <h2>Step 9. SuiteCRM Configuration</h2>
                <h3>Customize Login Icons</h3>
                <p>Pay attention to that, if custom scripts are not enabled, nothing will be showed.
                    Customize shape, space between icons and size of the login icons.</p>
                <p><img alt="SuiteCRMConfiguration" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/docu/d10.png" />  </p>
                <h2>Step 10. Show icons in frontend</h2>
                <p><img alt="frontend" src="https://raw.githubusercontent.com/GluuFederation/gluu-sso-SuiteCRM-module/master/docu/d11.png" /></p>
            </div>
        </div>
        <!-- END of Container Page -->
    </div>
    <!-- END of Container -->
</div>
