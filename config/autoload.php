<?php

/**
 * rte_table extension for Contao Open Source CMS
 *
 * @copyright Copyright (c) 2008-2016, terminal42 gmbh
 * @author    terminal42 gmbh <info@terminal42.ch>
 * @license   http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link      http://github.com/terminal42/rte_table
 */

/**
 * Register the namespace
 */
ClassLoader::addNamespace('Terminal42\RteTable');

/**
 * Register the classes
 */
ClassLoader::addClasses([
    'Terminal42\RteTable\RteEditor'      => 'system/modules/rte_table/src/RteEditor.php',
    'Terminal42\RteTable\RteTableWizard' => 'system/modules/rte_table/src/RteTableWizard.php',
]);

/**
 * Register the templates
 */
TemplateLoader::addFiles([
    'be_rte_table_editor' => 'system/modules/rte_table/templates/backend',
]);