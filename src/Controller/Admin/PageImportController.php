<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Documents\Import\PageImporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\PageRepository;
use Pimcore\Model\Document\Page;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PageImportController
{
    public function __construct(
        private PageImporter $pageImporter,
        private PageRepository $pageRepository,
    ) {
    }

    #[Route(
        '/admin/neusta/import-export/page/import',
        name: 'neusta_pimcore_import_export_page_import',
        methods: ['POST']
    )]
    public function import(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file || !$file instanceof UploadedFile) {
            return new JsonResponse(['success' => false, 'message' => 'No file uploaded'], 400);
        }

        $overwrite = $request->request->getBoolean('overwrite');

        try {
            $page = $this->pageImporter->parseYaml($file->getContent());

            $message = $this->replaceIfExists($page, $overwrite);

            return new JsonResponse(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    protected function replaceIfExists(Page $page, bool $overwrite): string
    {
        $oldPage = $this->pageRepository->getByPath('/' . $page->getFullPath());
        if (null !== $oldPage) {
            if ($overwrite) {
                $oldPage->delete();
                $page->save();

                return 'Document replaced successfully';
            }

            return 'Document already exists and was not replaced';
        }
        $page->save();

        return 'New Document imported successfully';
    }
}
