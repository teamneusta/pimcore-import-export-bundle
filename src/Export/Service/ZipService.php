<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Export\Service;

use Pimcore\Model\Asset;

class ZipService
{
    /**
     * @param array<Asset> $assets
     */
    public function createZipWithAssets(array $assets, string $yamlContent, string $zipFilename): void
    {
        // Delete the existing file if it exists
        if (file_exists($zipFilename)) {
            unlink($zipFilename);
        }

        $zip = new \ZipArchive();
        if (true !== $zip->open($zipFilename, \ZipArchive::CREATE)) {
            throw new \RuntimeException('Could not create ZIP file');
        }

        // Add YAML file to ZIP
        $zip->addFromString('export.yaml', $yamlContent);

        // Add physical representation of assets to ZIP
        foreach ($assets as $asset) {
            $stream = $asset->getStream();
            if (\is_resource($stream)) {
                $content = stream_get_contents($stream);
                if ($content && !empty($asset->getFilename())) {
                    $zip->addFromString($asset->getType() . \DIRECTORY_SEPARATOR . basename($asset->getFilename()), $content);
                }
                fclose($stream);
            }
        }

        $zip->close();
    }
}
