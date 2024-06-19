<?php

declare(strict_types=1);

namespace Terminal42\RteTableBundle\Widget;

use Contao\StringUtil;
use Contao\System;
use Contao\TableWizard;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Terminal42\RteTableBundle\Controller\RteEditorController;

class RteTableWizard extends TableWizard
{
    public function generate(): string
    {
        if (!$GLOBALS['TL_CONFIG']['useRTE']) {
            return parent::generate();
        }

        $container = System::getContainer();
        $packages = $container->get('assets.packages');

        $url = $container->get('router')->generate(
            RteEditorController::class,
            [
                'table' => $this->strTable,
                'field' => $this->strField,
                'id' => $this->objDca->id,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $GLOBALS['TL_CSS'][] = $packages->getUrl('table-wizard.css', 'terminal42_rte_table');
        $GLOBALS['TL_JAVASCRIPT'][] = $packages->getUrl('table-wizard.js', 'terminal42_rte_table');

        return str_replace(
            '<div id="tl_tablewizard">',
            sprintf(
                '<div id="tl_tablewizard" data-rte-url="%s" data-rte-label="%s">',
                $container->get('uri_signer')->sign($url),
                StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['rte_table_edit']),
            ),
            parent::generate(),
        );
    }
}
