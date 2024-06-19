<?php

declare(strict_types=1);

namespace Terminal42\RteTableBundle\Controller;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Picker\PickerBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UriSigner;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/%contao.backend.route_prefix%/rte-table-editor/{table}/{field}/{id}', defaults: ['_scope' => 'backend'])]
#[AsController]
#[Autoconfigure(bind: ['$charset' => '%kernel.charset%'])]
class RteEditorController extends AbstractController
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly TranslatorInterface $translator,
        private readonly PickerBuilderInterface $pickerBuilder,
        private readonly UriSigner $uriSigner,
        private readonly string $charset,
    ) {
    }

    public function __invoke(Request $request, string $table, string $field, int $id): Response
    {
        if (!$this->uriSigner->checkRequest($request)) {
            throw $this->createNotFoundException();
        }

        $this->denyAccessUnlessGranted('ROLE_USER');

        $this->framework->initialize();

        Controller::loadDataContainer($table);

        $template = new BackendTemplate('be_rte_table_editor');
        $template->language = $request->getLocale();
        $template->charset = $this->charset;
        $template->title = $this->translator->trans('MSC.rte_table_edit', [], 'contao_default');
        $template->theme = Backend::getTheme();
        $template->rte = $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['rte'] ?? 'be_tinyMCE';
        $template->selector = 'rte-table-editor';
        $template->fileBrowserTypes = implode(' ', $this->getFileBrowserTypes());
        $template->source = $table.'.'.$id;

        return $template->getResponse();
    }

    private function getFileBrowserTypes(): array
    {
        $fileBrowserTypes = [];

        foreach (['file' => 'image', 'link' => 'file'] as $context => $fileBrowserType) {
            if ($this->pickerBuilder->supportsContext($context)) {
                $fileBrowserTypes[] = $fileBrowserType;
            }
        }

        return $fileBrowserTypes;
    }
}
