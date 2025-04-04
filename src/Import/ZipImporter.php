<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import;

use Symfony\Component\Filesystem\Filesystem;

class ZipImporter
{
    private Filesystem $filesystem;
    private string $tempDir;

    public function __construct(?string $tempDir = null)
    {
        $this->filesystem = new Filesystem();
        $this->tempDir = $tempDir ?? sys_get_temp_dir();
    }

    /**
     * @return array<string, mixed>
     */
    public function import(string $zipPath): array
    {
        $extractPath = $this->createTempExtractPath();

        $zip = new \ZipArchive();
        if (true !== $zip->open($zipPath)) {
            throw new \RuntimeException("Konnte ZIP-Datei nicht öffnen: $zipPath");
        }

        $zip->extractTo($extractPath);
        $zip->close();

        $yamlPath = $this->findYamlFile($extractPath);
        if (!$yamlPath) {
            throw new \RuntimeException('Keine YAML-Datei im ZIP gefunden.');
        }

        $yamlData = file_get_contents($yamlPath);
        if ($yamlData === false) {
            throw new \RuntimeException("Failed to read YAML file: $yamlPath");
        }

        $assets = $this->collectAssetDirectories($extractPath);

        return array_merge(['yaml' => $yamlData], $assets);
    }

    private function createTempExtractPath(): string
    {
        $extractPath = $this->tempDir . '/zipimport_' . uniqid();
        $this->filesystem->mkdir($extractPath);

        return $extractPath;
    }

    private function findYamlFile(string $dir): ?string
    {
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($rii as $file) {
            if ($file->isFile() && 'yaml' === strtolower($file->getExtension())) {
                return $file->getPathname();
            }
        }

        return null;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function collectAssetDirectories(string $basePath): array
    {
        $entries = scandir($basePath);
        if (!$entries) {
            return [];
        }

        $assets = [];
        foreach ($entries as $entry) {
            if (\in_array($entry, ['.', '..'], true)) {
                continue;
            }

            $fullPath = $basePath . \DIRECTORY_SEPARATOR . $entry;

            // Nur Verzeichnisse betrachten
            if (!is_dir($fullPath)) {
                continue;
            }

            $files = $this->collectFilesFromDir($fullPath);
            if (!empty($files)) {
                $assets[$entry] = $files;
            }
        }

        return $assets;
    }

    /**
     * @return array<string, string>
     */
    private function collectFilesFromDir(string $dir): array
    {
        $files = [];
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($rii as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();
                $content = file_get_contents($file->getPathname());
                if ($content !== false) {
                    $files[$filename] = $content;
                }
            }
        }

        return $files;
    }
}
