<?php

/**
 * rte_table extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/rte_table
 */

namespace Terminal42\RteTable;

use Contao\Ajax;
use Contao\Backend;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\Controller;
use Contao\Environment;
use Contao\Input;
use Contao\Model;
use Contao\System;

class RteEditor extends Backend
{

    /**
     * Current Ajax object
     * @var Ajax
     */
    protected $objAjax;

    /**
     * Initialize the controller
     *
     * 1. Import the user
     * 2. Call the parent constructor
     * 3. Authenticate the user
     * 4. Load the language files
     * DO NOT CHANGE THIS ORDER!
     */
    public function __construct()
    {
        $this->import('BackendUser', 'User');
        parent::__construct();

        $this->User->authenticate();
        System::loadLanguageFile('default');
    }

    /**
     * Run the controller and parse the template
     */
    public function run()
    {
        $template       = new BackendTemplate('be_main');
        $template->main = '';

        // Ajax request
        if ($_POST && Environment::get('isAjaxRequest')) {
            $this->objAjax = new Ajax(Input::post('action'));
            $this->objAjax->executePreActions();
        }

        $strTable = Input::get('table');
        $strField = Input::get('field');

        // Define the current ID
        define('CURRENT_ID', (Input::get('table') ? $this->Session->get('CURRENT_ID') : Input::get('id')));

        Controller::loadDataContainer($strTable);
        $strDriver     = 'DC_'.$GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'];
        $objDca        = new $strDriver($strTable);
        $objDca->field = $strField;

        // Set the active record
        if ($this->Database->tableExists($strTable)) {
            /** @var Model $strModel $strModel */
            $strModel = Model::getClassFromTable($strTable);

            if (class_exists($strModel)) {
                $objModel = $strModel::findByPk(Input::get('id'));

                if ($objModel !== null) {
                    $objDca->activeRecord = $objModel;
                }
            }
        }

        // AJAX request
        if ($_POST && Environment::get('isAjaxRequest')) {
            $this->objAjax->executePostActions($objDca);
        }

        // Contao 4 compatibility
        if (version_compare(VERSION,'4.4','>=')) {
            $fileBrowserTypes = array();
            $pickerBuilder = \System::getContainer()->get('contao.picker.builder');

            foreach (array('file' => 'image', 'link' => 'file') as $context => $fileBrowserType)
            {
                if ($pickerBuilder->supportsContext($context))
                {
                    $fileBrowserTypes[] = $fileBrowserType;
                }
            }

            /** @var BackendTemplate|object $partial */
            $partial = new \BackendTemplate('be_rte_table_editor_contao4');
            $partial->fileBrowserTypes = $fileBrowserTypes;
            $partial->source = $strTable . '.' . CURRENT_ID;
        } else {
            $partial = new BackendTemplate('be_rte_table_editor');
        }

        $template->isPopup  = true;
        $template->main     = $partial->parse();
        $template->theme    = Backend::getTheme();
        $template->base     = Environment::get('base');
        $template->language = $GLOBALS['TL_LANGUAGE'];
        $template->title    = specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']);
        $template->charset  = Config::get('characterSet');

        Config::set('debugMode', false);
        $template->output();
    }
}
