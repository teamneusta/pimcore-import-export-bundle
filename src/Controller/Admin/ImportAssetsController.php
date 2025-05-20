<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Controller\Admin\Base\AbstractImportBaseController;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Neusta\Pimcore\ImportExportBundle\Import\ZipImporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\AssetRepository;
use Pimcore\Model\Asset;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractImportBaseController<Asset>
 */
final class ImportAssetsController extends AbstractImportBaseController
{
    /**
     * @param Importer<\ArrayObject<int|string, mixed>, Asset> $importer
     */
    public function __construct(
        LoggerInterface $logger,
        private Importer $importer,
        private ZipImporter $zipImporter,
        AssetRepository $assetRepository,
    ) {
        parent::__construct($logger, $assetRepository, 'Asset');
    }

    #[Route(
        '/admin/neusta/import-export/asset/import',
        name: 'neusta_pimcore_import_export_asset_import',
        methods: ['POST']
    )]
    public function import(Request $request): JsonResponse
    {
        return parent::import($request);
    }

    protected function importByFile(UploadedFile $file, string $format): array
    {
        $extension = pathinfo($file->getClientOriginalName(), \PATHINFO_EXTENSION);

        // if file is ZIP add physical files to Assets
        if ('zip' === $extension) {
            $zipContent = $this->zipImporter->import($file->getPathname());
            $assets = $this->importer->import($zipContent['yaml'], $format);
            foreach ($assets as $asset) {
                if (
                    \array_key_exists($asset->getType(), $zipContent)
                    && $asset->getKey()
                    && \array_key_exists($asset->getKey(), $zipContent[$asset->getType()])
                ) {
                    $asset->setData(file_get_contents($zipContent[$asset->getType()][$asset->getKey()]->getRealPath()));
                }
            }

            return $assets;
        }

        // if file is not ZIP only create Assets
        try {
            $content = $file->getContent();

            return $this->importer->import($content, $format);
        } catch (\Exception $e) {
            throw new \Exception('Error reading uploaded file: ' . $e->getMessage(), 0, $e);
        }
    }

    protected function cleanUp(): void
    {
        $this->zipImporter->cleanUp();
    }
}
