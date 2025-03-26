<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\AssetRepository;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DocumentRepository;
use Pimcore\Model\Asset;
use Pimcore\Model\Document;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ImportAssetsController
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

    /**
     * @param Importer<\ArrayObject<int|string, mixed>, Document> $importer
     */
    public function __construct(
        private Importer $importer,
        private AssetRepository $assetRepository,
    ) {
    }

    #[Route(
        '/admin/neusta/import-export/asset/import',
        name: 'neusta_pimcore_import_export_asset_import',
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
            $assets = $this->importer->import($file->getContent(), (string) $request->query->get('format', 'yaml'));

            $results = [
                self::SUCCESS_DOCUMENT_REPLACEMENT => 0,
                self::SUCCESS_WITHOUT_REPLACEMENT => 0,
                self::SUCCESS_NEW_DOCUMENT => 0,
            ];
            foreach ($assets as $asset) {
                if ($asset instanceof Asset) {
                    $resultCode = $this->replaceIfExists($asset, $overwrite);
                    ++$results[$resultCode];
                }
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

    protected function replaceIfExists(Asset $asset, bool $overwrite): int
    {
        $oldAsset = $this->assetRepository->getByPath('/' . $asset->getFullPath());
        if (null !== $oldAsset) {
            if ($overwrite) {
                $oldAsset->delete();
                $asset->save();

                return self::SUCCESS_DOCUMENT_REPLACEMENT;
            }

            return self::SUCCESS_WITHOUT_REPLACEMENT;
        }
        $asset->save();

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
                    $start = 'One Asset';
                } else {
                    $start = \sprintf('%d Assets', $result);
                }
                $message = \sprintf('%s %s', $start, $this->messagesMap[$resultCode]);
                $resultMessage .= $message . '<br/><br/>';
            }
        }

        return '<p style="padding: 20px;">' . $resultMessage . '</p>';
    }
}
