<?php
if(!gluu_is_oxd_registered()){
    SugarApplication::redirect('index.php?module=Gluussos&action=general');
}
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

$get_scopes            = json_decode(select_query($db, 'gluu_scopes'),true);
$gluu_config           = json_decode(select_query($db, 'gluu_config'),true);
$gluu_acr              = json_decode(select_query($db, 'gluu_acr'),true);
$gluu_auth_type        = select_query($db, 'gluu_auth_type');
$gluu_send_user_check  = select_query($db, 'gluu_send_user_check');
$gluu_provider         = select_query($db, 'gluu_provider');
$gluu_user_role         = select_query($db, 'gluu_user_role');
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

<link href="modules/Gluussos/GluuOxd_Openid/css/gluu-oxd-css.css" rel="stylesheet"/>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="modules/Gluussos/GluuOxd_Openid/js/scope-custom-script.js"></script>
<script>
    var $m = jQuery.noConflict();
    var oxd_id = "<?php echo gluu_is_oxd_registered(); ?>";
    if (oxd_id) {
        voiddisplay("#configopenid");
        setactive('social-sharing-setup');
    } else {
        voiddisplay("#account_setup");
        setactive('account_setup');
    }
    $m(document).ready(function () {

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
</script>
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
            <li class="active" id="account_setup"><a data-method="#accountsetup">General</a></li>
            <li id="social-sharing-setup"><a data-method="#configopenid">OpenID Connect Configuration</a></li>
            <li id=""><a data-method="#configopenid" href="https://oxd.gluu.org/docs/plugin/suitecrm/" target="_blank">Documentation</a></li>
        </ul>
        <div class="container-page">
            <!-- General edit tab without client_id and client_secret -->
            <div class="page" id="accountsetup">
                <form id="register_GluuOxd" name="f" method="post" action="index.php?module=Gluussos&action=gluuPostData">
                    <input type="hidden" name="form_key" value="general_oxd_edit"/>
                    <fieldset style="border: 2px solid #53cc6b; padding: 20px">
                        <legend style="border-bottom:none; width: 110px !important;">
                            <img style=" height: 45px;" src="modules/Gluussos/GluuOxd_Openid/images/icons/gl.png"/>
                        </legend>
                        <table class="table">
                            <tr>
                                <td><label for="default_role"><b><font color="#FF0000">*</font>New User Default Role:</b></label></td>
                                <td>
                                    <?php
                                    $user_types = array(
                                        array('name'=>'Regular User', 'status'=>'0'),
                                        array('name'=>'System Administrator User', 'status'=>'1')
                                    );
                                    ?>
                                    <select id="UserType" name="gluu_user_role" >
                                        <?php
                                        foreach($user_types as $user_type){
                                            ?>
                                            <option <?php if($user_type['status'] == $gluu_user_role) echo 'selected'; ?> value="<?php echo $user_type['status'];?>"><?php echo $user_type['name'];?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                    <br/>
                                </td>
                            </tr>
                            <tr>
                                <td><b>URI of the OpenID Connect Provider:</b></td>
                                <td><input class="" type="url" name="gluu_provider" id="gluu_provider"
                                           autofocus="true" disabled placeholder="Enter URI of the OpenID Connect Provider."
                                           style="width:400px;"
                                           value="<?php echo $gluu_provider; ?>"/>
                                </td>
                            </tr>
                            <?php if(!empty($gluu_config['gluu_client_id']) and !empty($gluu_config['gluu_client_secret'])){?>
                                <tr>
                                    <td><b><font color="#FF0000">*</font>Client ID:</b></td>
                                    <td><input class="" type="text" name="gluu_client_id" id="gluu_client_id"
                                               autofocus="true" placeholder="Enter your client_id."
                                               style="width:400px; "
                                               value="<?php if(!empty($gluu_config['gluu_client_id'])) echo $gluu_config['gluu_client_id']; ?>"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><b><font color="#FF0000">*</font>Client Secret:</b></td>
                                    <td>
                                        <input class="" type="text" name="gluu_client_secret" id="gluu_client_secret"
                                               autofocus="true" placeholder="Enter your client_secret."  style="width:400px; " value="<?php if(!empty($gluu_config['gluu_client_secret'])) echo $gluu_config['gluu_client_secret']; ?>"/>
                                    </td>
                                </tr>
                            <?php }?>
                            <tr>
                                <td><b><font color="#FF0000">*</font>oxd port:</b></td>
                                <td>
                                    <input class="" type="number"  name="gluu_oxd_port" min="0" max="65535"
                                           value="<?php echo $gluu_config['gluu_oxd_port']; ?>"
                                           style="width:400px;" placeholder="Please enter free port (for example 8099). (Min. number 0, Max. number 65535)."/>
                                </td>
                            </tr>
                            <tr>
                                <td><b>oxd id:</b></td>
                                <td>
                                    <input class="" type="text" disabled name="oxd_id"
                                           value="<?php echo gluu_is_oxd_registered(); ?>"
                                           style="width:400px;background-color: rgb(235, 235, 228);" placeholder="Your oxd id"/>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div><input type="submit" name="saveButton" value="Save" style="float: right; width: 120px; background-color:#00AA00 !important;" class=""/></div>
                                </td>
                                <td>
                                    <a class="button button-primary button-large" style="text-align:center; float: left; width: 120px" href="index.php?module=Gluussos&action=general">Cancel</a>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                </form>
            </div>
            <div class="page" style="display: none" id="configopenid">
                <?php if (!gluu_is_oxd_registered()){ ?>
                    <div class="mess_red">
                        Please enter the details of your OpenID Connect Provider.
                    </div><br/>
                <?php } ?>
                <div>
                    <form action="index.php?module=Gluussos&action=gluuPostData" method="post"
                          enctype="multipart/form-data">
                        <input type="hidden" name="form_key" value="openid_config_page"/>

                        <fieldset style="border: 2px solid #53cc6b; padding: 20px">
                            <legend style="border-bottom:none; width: 110px !important;">
                                <img style=" height: 45px;" src="modules/Gluussos/GluuOxd_Openid/images/icons/gl.png"/>
                            </legend>
                            <div class="entry-edit" >
                                <div class="entry-edit-head" style="background-color: #00aa00 !important;">
                                    <h4 class="icon-head head-edit-form fieldset-legend">Requested scopes</h4>
                                </div>
                                <div class="fieldset">
                                    <div class="hor-scroll">
                                        <div >
                                            <label   for="openid">
                                                <input checked type="checkbox" name=""  id="openid" value="openid"  disabled />
                                                <input type="hidden"  name="scope[]"  value="openid" />openid
                                            </label><label  for="profile">
                                                <input checked type="checkbox" name=""  id="profile" value="profile"  disabled />
                                                <input type="hidden"  name="scope[]"  value="profile" />profile
                                            </label><label  for="email">
                                                <input checked type="checkbox" name=""  id="email" value="email"  disabled />
                                                <input type="hidden"  name="scope[]"  value="email" />email
                                            </label>
                                            <?php foreach($get_scopes as $scop) :?>
                                                <?php if ($scop == 'openid' or $scop == 'email' or $scop == 'profile'){?>
                                                <?php } else{?>
                                                    <label  for="<?php echo $scop."_1";?>">
                                                        <input <?php if($gluu_config && in_array($scop, $gluu_config['config_scopes'])){ echo "checked";} ?> type="checkbox" name="scope[]"  id="<?php echo $scop."_1";?>" value="<?php echo $scop;?>" <?php if (!gluu_is_oxd_registered() || $scop=='openid') echo ' disabled '; ?> />
                                                        <?php echo $scop;?></label>
                                                <?php }
                                            endforeach;?>
                                        </div>
                                        <script type="application/javascript">
                                            jQuery(document).ready(function(){
                                                jQuery("#show_scope_table").click(function(){
                                                    jQuery("#scope_table").toggle();
                                                });
                                                jQuery("#show_acr_table").click(function(){
                                                    jQuery("#acr_table").toggle();
                                                });
                                            });
                                        </script>
                                        <div>
                                            <br/>
                                            <input type="button" value="Delete scope" style="width: 100px;" id="show_scope_table"/>
                                            <br/><br/>
                                        </div>
                                        <div id="scope_table" style="display: none">
                                            <table class="form-list" style="text-align: center">
                                                <tr class="wrapper-tr" style="text-align: center">
                                                    <th style="border: 1px solid #43ffdf; width: 70px;text-align: center"><h3>N</h3></th>
                                                    <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"><h3>Name</h3></th>
                                                    <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"><h3>Delete</h3></th>
                                                </tr>
                                                <tr>
                                                    <th style="border: 1px solid #43ffdf; padding: 0px; width: 70px">1</th>
                                                    <th style="border: 1px solid #43ffdf;width: 200px;text-align: center">openid</th>
                                                    <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"></th>
                                                </tr>
                                                <tr>
                                                    <th style="border: 1px solid #43ffdf; padding: 0px; width: 70px">2</th>
                                                    <th style="border: 1px solid #43ffdf;width: 200px;text-align: center">profile</th>
                                                    <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"><h3></h3></th>
                                                </tr>
                                                <tr>
                                                    <th style="border: 1px solid #43ffdf; padding: 0px; width: 70px">3</th>
                                                    <th style="border: 1px solid #43ffdf;width: 200px;text-align: center">email</th>
                                                    <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"></th>
                                                </tr>
                                                <?php
                                                $n = 3;
                                                foreach($get_scopes as $scop) :?>
                                                    <?php if ($scop == 'openid' or $scop == 'email' or $scop == 'profile'){?>
                                                    <?php } else{
                                                        $n++;
                                                        ?>
                                                        <tr class="wrapper-trr">
                                                            <td style="border: 1px solid #43ffdf; padding: 0px; width: 70px"><h3><?php echo $n; ?></h3></td>
                                                            <td style="border: 1px solid #43ffdf; padding: 0px; width: 200px"><h3><label for="<?php echo $scop; ?>"><?php echo $scop; ?></label></h3></td>
                                                            <td style="border: 1px solid #43ffdf; padding: 0px; width: 200px">
                                                                <?php if ($n == 4): ?>
                                                                    <form></form>
                                                                <?php endif; ?>
                                                                <form
                                                                    action="index.php?module=Gluussos&action=gluuPostData"
                                                                    method="post">
                                                                    <input type="hidden" name="form_key_scope"
                                                                           value="openid_config_delete_scop"/>
                                                                    <input type="hidden"
                                                                           value="<?php echo $scop; ?>"
                                                                           name="value_scope"/>
                                                                    <?php if ($scop != 'openid'){ ?>
                                                                        <input  style="width: 100px; background-color: red !important; cursor: pointer"
                                                                                type="submit"
                                                                                class="button button-primary " <?php if (!gluu_is_oxd_registered()) echo 'disabled' ?>
                                                                                value="Delete" name="delete_scop"/>
                                                                    <?php }  ?>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    <?php }


                                                endforeach;
                                                ?>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="fieldset">
                                    <input type="button" id="adding" class="button button-primary button-large add" style="width: 100px;" value="Add scopes"/>
                                    <div class="hor-scroll">
                                        <table class="form-list5 container">
                                            <tr class="wrapper-tr">
                                                <td class="value">
                                                    <input type="text" style='margin-left: -12px; width: 100px;' placeholder="Scope name" name="scope_name[]"/>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="entry-edit-head" style="background-color: #00aa00 !important;">
                                    <h4 class="icon-head head-edit-form fieldset-legend">Manage Authentication</h4>
                                </div>
                                <div class="fieldset">
                                    <div style="margin-right: 30px">
                                        <p style="font-weight:bold "><input type="checkbox" name="send_user_check" id="send_user" value="1" <?php if(!gluu_is_oxd_registered()) echo 'disabled';?> <?php if($gluu_send_user_check) echo 'checked';?> /><label for="send_user"> Send user straight to OpenID Provider for authentication</label>
                                        </p>
                                        <br/>
                                        <table>
                                            <tr >
                                                <label for="send_user_type"><p style="font-weight:bold ">Select acr</p></label>
                                                <br/>
                                                <span>To signal which type of authentication should be used, an OpenID Connect client may request a specific authentication context class reference value or "acr".</span>
                                                <br/><br/>
                                                <?php
                                                if(!empty($gluu_acr)){
                                                    ?>
                                                    <select name="send_user_type" style="width: 100px;" id="send_user_type" <?php if(!gluu_is_oxd_registered()) echo 'disabled'?>>
                                                        <option value="default">none</option>
                                                        <?php
                                                        foreach($gluu_acr as $custom_script){
                                                            if($custom_script != "default"){
                                                                ?>
                                                                <option <?php if($gluu_auth_type == $custom_script) echo 'selected'; ?> value="<?php echo $custom_script;?>"><?php echo $custom_script;?></option>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                <?php } ?>
                                            </tr>
                                        </table>
                                        <div>
                                            <br/>
                                            <input type="button" value="Delete acr" style="width: 100px;" id="show_acr_table"/>
                                            <br/><br/>
                                        </div>
                                        <div id="acr_table" style="display: none">
                                            <table class="form-list" style="text-align: center">
                                                <tr class="wrapper-tr" style="text-align: center">
                                                    <th style="border: 1px solid #43ffdf; width: 70px;text-align: center"><h3>N</h3></th>
                                                    <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"><h3>Acr Value</h3></th>
                                                    <th style="border: 1px solid #43ffdf;width: 200px;text-align: center"><h3>Delete</h3></th>
                                                </tr>
                                                <?php
                                                $n = 0;
                                                foreach ($gluu_acr as $custom_script) {
                                                    $n++;
                                                    ?>
                                                    <tr class="wrapper-trr">
                                                        <td style="border: 1px solid #43ffdf; padding: 0px; width: 70px"><h3><?php echo $n; ?></h3></td>
                                                        <td style="border: 1px solid #43ffdf; padding: 0px; width: 200px"><h3><?php echo $custom_script; ?></h3></td>
                                                        <td style="border: 1px solid #43ffdf; padding: 0px; width: 200px">
                                                            <?php if ($n == 1): ?>
                                                                <form></form>
                                                            <?php endif; ?>
                                                            <form
                                                                action="index.php?module=Gluussos&action=gluuPostData"
                                                                method="post">
                                                                <input type="hidden" name="form_key_acr"
                                                                       value="openid_config_delete_custom_scripts"/>
                                                                <input type="hidden"
                                                                       value="<?php echo $custom_script; ?>"
                                                                       name="value_script"/>
                                                                <input
                                                                    style="width: 100px; background-color: red !important; cursor: pointer"
                                                                    type="submit"
                                                                    class="button button-primary "
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
                                </div>

                                <div class="fieldset">
                                    <div class="hor-scroll">
                                        <input type="button" class="button button-primary button-large " style="width: 100px" id="adder" value="Add acr"/>
                                        <table class="form-list1 container">
                                            <tr class="count_scripts wrapper-trr">
                                                <td class="value">
                                                    <input style='margin-left: -12px; width: 100px;' type="text" placeholder="Acr value" name="acr_name[]"/>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <br/>

                            </div>
                            <div>
                                <input class="set_oxd_config" style="width: 100px" type="submit" class="button button-primary button-large" <?php if (!gluu_is_oxd_registered()) echo 'disabled' ?> value="Save" name="set_oxd_config"/>
                                <br/>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
        <!-- END of Container Page -->
    </div>
    <!-- END of Container -->
</div>
