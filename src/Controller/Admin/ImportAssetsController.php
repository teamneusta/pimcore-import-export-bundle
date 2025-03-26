<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Controller\Admin\Base\AbstractImportBaseController;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Neusta\Pimcore\ImportExportBundle\Import\ZipImporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\AssetRepository;
use Pimcore\Model\Document;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use ZipArchive;

final class ImportAssetsController extends AbstractImportBaseController
{
    /**
     * @param Importer<\ArrayObject<int|string, mixed>, Document> $importer
     */
    public function __construct(
        private Importer $importer,
        private ZipImporter $zipImporter,
        AssetRepository $assetRepository,
    ) {
        parent::__construct($assetRepository);
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
        $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);

        // if file is ZIP add physical files to Assets
        if ($extension === 'zip') {
            $zipContent = $this->zipImporter->import($file->getPathname());
            $assets = $this->importer->import($zipContent['yaml'], $format);
            foreach ($assets as $asset) {
                if (
                    array_key_exists($asset->getType(), $zipContent) &&
                    array_key_exists($asset->getKey(), $zipContent[$asset->getType()])
                ) {
                    $asset->setData($zipContent[$asset->getType()][$asset->getKey()]);
                    if ($this->overwrite) {
                        $asset->save(["versionNote" => "added by pimcore-import-export-bundle"]);
                    }
                }
            }
            return $assets;
        }

        // if file is not ZIP only create Assets
        return $this->importer->import($file->getContent(), $format);
    }
}
