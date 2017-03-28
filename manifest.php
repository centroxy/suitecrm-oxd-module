<?php
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
	$manifest = array(
	    'acceptable_sugar_flavors' => array('CE', 'PRO', 'CORP', 'ENT', 'ULT'),
	    'acceptable_sugar_versions' => array(
	        'exact_matches' => array(),
	        'regex_matches' => array(
	            0 => '6\\.5\\.(.*?)',
	            1 => '6\\.7\\.(.*?)',
	            2 => '7\\.2\\.(.*?)',
	            3 => '7\\.2\\.(.*?)\\.(.*?)',
	            4 => '7\\.5\\.(.*?)\\.(.*?)',
	            5 => '7\\.6\\.(.*?)\\.(.*?)'
	        )
	    ),
	    'author' => 'Gluu',
	    'description' => 'This module will enable you to authenticate users against any standard OpenID Connect Provider.',
	    'icon' => '',
	    'is_uninstallable' => true,
	    'name' => 'OpenID Connect SSO by Gluu',
	    'published_date' => '2016-05-11 20:45:04',
	    'type' => 'module',
	    'version' => '3.0.1',
	    'remove_tables' => 'prompt',
	
	);
	
	$installdefs = array (
    'id'=> 'gluusso',
  'copy' =>
  array (
    array (
      'from' => '<basepath>/custom/modules/Users/Login.php',
      'to' => 'custom/modules/Users/Login.php',
    ),
    array (
       'from' => '<basepath>/custom/modules/Users/Logout.php',
       'to' => 'custom/modules/Users/Logout.php',
    ),
    array (
      'from' => '<basepath>/custom/include/globalControlLinks.php',
      'to' => 'custom/include/globalControlLinks.php',
    ),
      array (
          'from' => '<basepath>/custom/application/Ext/Include/modules.ext.php',
          'to' => 'custom/application/Ext/Include/modules.ext.php',
      ),
      array (
          'from' => '<basepath>/modules/Gluussos',
          'to' => 'modules/Gluussos',
      ),
      array (
          'from' => '<basepath>/gluu.php',
          'to' => 'gluu.php',
      ),
      array (
          'from' => '<basepath>/gluu_logout.php',
          'to' => 'gluu_logout.php',
      ),
  ),
);




