<?php

/**
 * rte_table extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/rte_table
 */

// Set the script name
define('TL_SCRIPT', 'system/modules/rte_table/public/wizard.php');

// Initialize the system
define('TL_MODE', 'BE');
require dirname(__DIR__) . '/../../../system/initialize.php';

// Run the controller
$controller = new \Terminal42\RteTable\RteEditor;
$controller->run();