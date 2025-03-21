<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Documents\Import\PageImporter;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\PageRepository;
use Pimcore\Model\Document\Page as PimcorePage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class PageImportController
{
    public const ERR_NO_FILE_UPLOADED = 1;
    public const SUCCESS_DOCUMENT_REPLACEMENT = 2;
    public const SUCCESS_WITHOUT_REPLACEMENT = 3;
    public const SUCCESS_NEW_DOCUMENT = 4;

    /**
     * @var string[] Map of error codes to messages
     */
    private array $messagesMap = [
        self::ERR_NO_FILE_UPLOADED => 'No file uploaded',
        self::SUCCESS_DOCUMENT_REPLACEMENT => 'replaced successfully',
        self::SUCCESS_WITHOUT_REPLACEMENT => 'not replaced',
        self::SUCCESS_NEW_DOCUMENT => 'imported successfully',
    ];

    public function __construct(
        private Importer $pageImporter,
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
        $file = $this->getUploadedFile($request);
        if (!$file instanceof UploadedFile) {
            return $this->createJsonResponse(false, $this->messagesMap[self::ERR_NO_FILE_UPLOADED], 400);
        }

        $overwrite = $request->request->getBoolean('overwrite');

        try {
            $pages = $this->pageImporter->import($file->getContent(), (string)$request->query->get('format', 'yaml'));

            $results = [
                self::SUCCESS_DOCUMENT_REPLACEMENT => 0,
                self::SUCCESS_WITHOUT_REPLACEMENT => 0,
                self::SUCCESS_NEW_DOCUMENT => 0,
            ];
            foreach ($pages as $page) {
                $resultCode = $this->replaceIfExists($page, $overwrite);
                ++$results[$resultCode];
            }
            $resultMessage = $this->appendMessage($results);

            return $this->createJsonResponse(true, $resultMessage);
        } catch (\Exception $e) {
            return $this->createJsonResponse(false, $e->getMessage(), 500);
        }
    }

    private function getUploadedFile(Request $request): ?UploadedFile
    {
        return $request->files->get('file');
    }

    private function createJsonResponse(bool $success, string $message, int $statusCode = 200): JsonResponse
    {
        return new JsonResponse(['success' => $success, 'message' => $message], $statusCode);
    }

    protected function replaceIfExists(PimcorePage $page, bool $overwrite): int
    {
        $oldPage = $this->pageRepository->getByPath('/' . $page->getFullPath());
        if (null !== $oldPage) {
            if ($overwrite) {
                $oldPage->delete();
                $page->save();

                return self::SUCCESS_DOCUMENT_REPLACEMENT;
            }

            return self::SUCCESS_WITHOUT_REPLACEMENT;
        }
        $page->save();

        return self::SUCCESS_NEW_DOCUMENT;
    }

    /**
     * @param array<int, int> $results
     */
    private function appendMessage(array $results): string
    {
        $resultMessage = '';

        foreach ($results as $resultCode => $result) {
            if ($result > 0) {
                if (1 === $result) {
                    $start = 'One Document';
                } else {
                    $start = \sprintf('%d Documents', $result);
                }
                $message = \sprintf('%s %s', $start, $this->messagesMap[$resultCode]);
                $resultMessage .= $message . '<br/><br/>';
            }
        }

        return '<p style="padding: 20px;">' . $resultMessage . '</p>';
    }
}
