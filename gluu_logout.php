<?php
/**
	 * @copyright Copyright (c) 2017, Gluu Inc. (https://gluu.org/)
	 * @license	  MIT   License            : <http://opensource.org/licenses/MIT>
	 *
	 * @package	  OpenID Connect SSO Module by Gluu
	 * @category  Module for SuiteCrm
	 * @version   3.0.0
	 *
	 * @author    Gluu Inc.          : <https://gluu.org>
	 * @link      Oxd site           : <https://oxd.gluu.org>
	 * @link      Documentation      : <https://oxd.gluu.org/docs/plugin/sugarcrm/>
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