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

use Contao\Backend;
use Contao\Environment;
use Contao\TableWizard;

class RteTableWizard extends TableWizard
{
    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        if (version_compare(VERSION,'4.4','>=')) {
            return $this->generateForContao4();
        }

        $arrColButtons = array('ccopy', 'cmovel', 'cmover', 'cdelete');
        $arrRowButtons = array('rcopy', 'rdrag', 'rup', 'rdown', 'rdelete');

        $strCommand = 'cmd_' . $this->strField;

        // Change the order
        if (\Input::get($strCommand) && is_numeric(\Input::get('cid')) && \Input::get('id') == $this->currentRecord)
        {
            $this->import('Database');

            switch (\Input::get($strCommand))
            {
                case 'ccopy':
                    for ($i=0, $c=count($this->varValue); $i<$c; $i++)
                    {
                        $this->varValue[$i] = array_duplicate($this->varValue[$i], \Input::get('cid'));
                    }
                    break;

                case 'cmovel':
                    for ($i=0, $c=count($this->varValue); $i<$c; $i++)
                    {
                        $this->varValue[$i] = array_move_up($this->varValue[$i], \Input::get('cid'));
                    }
                    break;

                case 'cmover':
                    for ($i=0, $c=count($this->varValue); $i<$c; $i++)
                    {
                        $this->varValue[$i] = array_move_down($this->varValue[$i], \Input::get('cid'));
                    }
                    break;

                case 'cdelete':
                    for ($i=0, $c=count($this->varValue); $i<$c; $i++)
                    {
                        $this->varValue[$i] = array_delete($this->varValue[$i], \Input::get('cid'));
                    }
                    break;

                case 'rcopy':
                    $this->varValue = array_duplicate($this->varValue, \Input::get('cid'));
                    break;

                case 'rup':
                    $this->varValue = array_move_up($this->varValue, \Input::get('cid'));
                    break;

                case 'rdown':
                    $this->varValue = array_move_down($this->varValue, \Input::get('cid'));
                    break;

                case 'rdelete':
                    $this->varValue = array_delete($this->varValue, \Input::get('cid'));
                    break;
            }

            $this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")
                ->execute(serialize($this->varValue), $this->currentRecord);

            $this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', \Environment::get('request'))));
        }

        // Make sure there is at least an empty array
        if (!is_array($this->varValue) || empty($this->varValue))
        {
            $this->varValue = array(array(''));
        }

        // Initialize the tab index
        if (!\Cache::has('tabindex'))
        {
            \Cache::set('tabindex', 1);
        }

        $tabindex = \Cache::get('tabindex');

        // Begin the table
        $return = '<div id="tl_tablewizard">
  <table id="ctrl_'.$this->strId.'" class="tl_tablewizard rte-table-wizard">
  <thead>
    <tr>';

        // Add column buttons
        for ($i=0, $c=count($this->varValue[0]); $i<$c; $i++)
        {
            $return .= '
      <td style="text-align:center; white-space:nowrap">';

            // Add column buttons
            foreach ($arrColButtons as $button)
            {
                $return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['tw_'.$button]).'" onclick="Backend.tableWizard(this,\''.$button.'\',\'ctrl_'.$this->strId.'\');return false">'.\Image::getHtml(substr($button, 1).'.gif', $GLOBALS['TL_LANG']['MSC']['tw_'.$button], 'class="tl_tablewizard_img"').'</a> ';
            }

            $return .= '</td>';
        }

        $return .= '
      <td></td>
    </tr>
  </thead>
  <tbody class="sortable" data-tabindex="'.$tabindex.'">';

        // Add rows
        for ($i=0, $c=count($this->varValue); $i<$c; $i++)
        {
            $return .= '
    <tr>';

            // Add cells
            for ($j=0, $d=count($this->varValue[$i]); $j<$d; $j++)
            {
                $href = 'system/modules/rte_table/public/wizard.php?table='.$this->objDca->table.'&amp;field='.$this->objDca->field.'&amp;id='.$this->currentRecord.'&amp;popup=1';

                $return .= '
      <td class="tcontainer">
      <a href="#" class="rte-edit" onclick="RteTableWizard.edit({\'url\':\''.$href.'\',\'width\':768,\'height\':500,\'title\':\''.specialchars($GLOBALS['TL_LANG']['MSC']['rte_table_wizard.edit']).'\',\'el\':this});return false;"><span>'.$GLOBALS['TL_LANG']['MSC']['rte_table_wizard.edit'].'</span></a>
      <div class="rte-content">'.$this->varValue[$i][$j].'</div>
      <textarea name="'.$this->strId.'['.$i.']['.$j.']" class="tl_textarea noresize" tabindex="'.$tabindex++.'" rows="'.$this->intRows.'" cols="'.$this->intCols.'"'.$this->getAttributes().'>'.specialchars($this->varValue[$i][$j]).'</textarea>
      </td>';
            }

            $return .= '
      <td style="white-space:nowrap">';

            // Add row buttons
            foreach ($arrRowButtons as $button)
            {
                $class = ($button == 'rup' || $button == 'rdown') ? ' class="button-move"' : '';

                if ($button == 'rdrag')
                {
                    $return .= \Image::getHtml('drag.gif', '', 'class="drag-handle" title="' . sprintf($GLOBALS['TL_LANG']['MSC']['move']) . '"');
                }
                else
                {
                    $return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'"' . $class . ' title="'.specialchars($GLOBALS['TL_LANG']['MSC']['tw_'.$button]).'" onclick="Backend.tableWizard(this,\''.$button.'\',\'ctrl_'.$this->strId.'\');return false">'.\Image::getHtml(substr($button, 1).'.gif', $GLOBALS['TL_LANG']['MSC']['tw_'.$button], 'class="tl_tablewizard_img"').'</a> ';
                }
            }

            $return .= '</td>
    </tr>';
        }

        // Store the tab index
        \Cache::set('tabindex', $tabindex);

        $return .= '
  </tbody>
  </table>
  </div>
  <script src="system/modules/rte_table/assets/table-wizard.js"></script>
  <script>Backend.tableWizardResize()</script>';

        $GLOBALS['TL_CSS'][] = 'system/modules/rte_table/assets/table-wizard.css';

        return $return;
    }

    /**
     * Generate the widget and return it as string (Contao 4)
     *
     * @return string
     */
    public function generateForContao4()
    {
        $arrColButtons = array('ccopy', 'cmovel', 'cmover', 'cdelete');
		$arrRowButtons = array('rcopy', 'rdelete', 'rdrag');

		// Make sure there is at least an empty array
		if (empty($this->varValue) || !\is_array($this->varValue))
		{
			$this->varValue = array(array(''));
		}

		// Begin the table
		$return = '<div id="tl_tablewizard">
  <table id="ctrl_'.$this->strId.'" class="tl_tablewizard rte-table-wizard">
  <thead>
    <tr>';

		// Add column buttons
		for ($i=0, $c=\count($this->varValue[0]); $i<$c; $i++)
		{
			$return .= '
      <td>';

			// Add column buttons
			foreach ($arrColButtons as $button)
			{
				$return .= ' <button type="button" data-command="' . $button . '" class="tl_tablewizard_img" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_'.$button]) . '">' . \Image::getHtml(substr($button, 1).'.svg') . '</button>';
			}

			$return .= '</td>';
		}

		$return .= '
      <td></td>
    </tr>
  </thead>
  <tbody class="sortable">';

		// Add rows
		for ($i=0, $c=\count($this->varValue); $i<$c; $i++)
		{
			$return .= '
    <tr>';

			// Add cells
			for ($j=0, $d=\count($this->varValue[$i]); $j<$d; $j++)
			{
                $href = 'system/modules/rte_table/public/wizard.php?table='.$this->objDca->table.'&amp;field='.$this->objDca->field.'&amp;id='.$this->currentRecord.'&amp;popup=1';

				$return .= '
      <td class="tcontainer">
      <a href="#" class="rte-edit" onclick="RteTableWizard.edit({\'url\':\''.$href.'\',\'width\':768,\'height\':500,\'title\':\''.specialchars($GLOBALS['TL_LANG']['MSC']['rte_table_wizard.edit']).'\',\'el\':this});return false;"><span>'.$GLOBALS['TL_LANG']['MSC']['rte_table_wizard.edit'].'</span></a>
      <div class="rte-content">'.$this->varValue[$i][$j].'</div>
      <textarea name="'.$this->strId.'['.$i.']['.$j.']" class="tl_textarea noresize" rows="'.$this->intRows.'" cols="'.$this->intCols.'"'.$this->getAttributes().'>'.\StringUtil::specialchars($this->varValue[$i][$j]).'</textarea>
      </td>';
			}

			$return .= '
      <td>';

			// Add row buttons
			foreach ($arrRowButtons as $button)
			{
				if ($button == 'rdrag')
				{
					$return .= ' <button type="button" class="drag-handle" title="' . sprintf($GLOBALS['TL_LANG']['MSC']['move']) . '" aria-hidden="true">' . \Image::getHtml('drag.svg') . '</button>';
				}
				else
				{
					$return .= ' <button type="button" data-command="' . $button . '" class="tl_tablewizard_img" title="' . \StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['tw_'.$button]) . '">' . \Image::getHtml(substr($button, 1).'.svg') . '</button>';
				}
			}

			$return .= '</td>
    </tr>';
		}

		$return .= '
  </tbody>
  </table>
  </div>
  <script src="system/modules/rte_table/assets/table-wizard.js"></script>
  <script>Backend.tableWizard("ctrl_'.$this->strId.'")</script>';

        $GLOBALS['TL_CSS'][] = 'system/modules/rte_table/assets/table-wizard.css';

		return $return;
    }
}
