<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\AssetRepository;
use Pimcore\Model\Asset;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class ExportAssetsController
{
    public function __construct(
        private Exporter $exporter,
        private AssetRepository $assetRepository,
    ) {
    }

    #[Route(
        '/admin/neusta/import-export/assets/export',
        name: 'neusta_pimcore_import_export_assets_export',
        methods: ['GET']
    )]
    public function export(Request $request): Response
    {
        $asset = $this->assetRepository->getById($request->query->getInt('asset_id'));
        if (!$asset instanceof Asset) {
            return new JsonResponse(
                \sprintf('Asset with id "%s" was not found', $request->query->getInt('asset_id')),
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->exportAssets([$asset], $request->query->getString('filename'), 'yaml');
    }

    #[Route(
        '/admin/neusta/import-export/assets/export/with-children',
        name: 'neusta_pimcore_import_export_assets_export_with_children',
        methods: ['GET']
    )]
    public function exportWithChildren(Request $request): Response
    {
        $asset = $this->assetRepository->getById($request->query->getInt('asset_id'));
        if (!$asset instanceof Asset) {
            return new JsonResponse(
                \sprintf('Asset with id "%s" was not found', $request->query->getInt('asset_id')),
                Response::HTTP_NOT_FOUND,
            );
        }

        // We need the list two times so generate an array first:
        $assets = iterator_to_array($this->assetRepository->findAllAssetsWithChildren($asset), false);

        return $this->exportAssets($assets, $request->query->getString('filename'), $request->query->getString('format'));
    }

    /**
     * @param array<Asset> $assets
     */
    private function exportAssets(array $assets, string $filename, string $format): Response
    {
        try {
            $yaml = $this->exporter->export($assets, $format);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $zipFilename = tempnam(sys_get_temp_dir(), 'export_') . '.zip';
        $zip = new \ZipArchive();
        if (true !== $zip->open($zipFilename, \ZipArchive::CREATE)) {
            return new JsonResponse('Could not create ZIP file', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Add YAML file to ZIP
        $zip->addFromString($filename, $yaml);

        // Add physical representation of assets to ZIP
        foreach ($assets as $asset) {
            if ('folder' !== $asset->getType()) {
                $stream = $asset->getStream();
                if (\is_resource($stream)) {
                    $content = stream_get_contents($stream);
                    if ($content) {
                        $zip->addFromString($asset->getType() . \DIRECTORY_SEPARATOR . basename($asset->getFilename()), $content);
                    }
                    fclose($stream);
                }
            }
        }

        $zip->close();

        return $this->createZipResponse($zipFilename, $filename . '.zip');
    }

    private function createZipResponse(string $zipFilename, string $downloadFilename): Response
    {
        $response = new BinaryFileResponse($zipFilename);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $downloadFilename
        );

        // Ensure the temporary file is deleted after the response is sent
        $response->deleteFileAfterSend(true);

        return $response;
    }
}
