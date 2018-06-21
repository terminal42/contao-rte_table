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

if (file_exists('../../../initialize.php')) {
    // Regular way
    /** @noinspection PhpIncludeInspection */
    require_once '../../../initialize.php';
} elseif (file_exists('../../../../system/initialize.php')) {
    // Contao 4 - Try composer location
    /** @noinspection PhpIncludeInspection */
    require_once '../../../../system/initialize.php';
} else {
    // Contao 3 - Try composer location
    /** @noinspection PhpIncludeInspection */
    require_once '../../../../../system/initialize.php';
}

// Run the controller
$controller = new \Terminal42\RteTable\RteEditor;
$controller->run();
