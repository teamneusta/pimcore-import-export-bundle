<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use ZipArchive;

class ZipImporter
{
    private Filesystem $filesystem;
    private string $tempDir;

    public function __construct(string $tempDir = null)
    {
        $this->filesystem = new Filesystem();
        $this->tempDir = $tempDir ?? sys_get_temp_dir();
    }

    public function import(string $zipPath): array
    {
        $extractPath = $this->createTempExtractPath();

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException("Konnte ZIP-Datei nicht öffnen: $zipPath");
        }

        $zip->extractTo($extractPath);
        $zip->close();

        $yamlPath = $this->findYamlFile($extractPath);
        if (!$yamlPath) {
            throw new \RuntimeException("Keine YAML-Datei im ZIP gefunden.");
        }

        $yamlData = file_get_contents($yamlPath);

        $assets = $this->collectAssetDirectories($extractPath, $yamlPath);

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
            if ($file->isFile() && strtolower($file->getExtension()) === 'yaml') {
                return $file->getPathname();
            }
        }
        return null;
    }

    private function collectAssetDirectories(string $basePath, string $yamlPath): array
    {
        $assets = [];
        $entries = scandir($basePath);
        foreach ($entries as $entry) {
            if (in_array($entry, ['.', '..'], true)) {
                continue;
            }

            $fullPath = $basePath . DIRECTORY_SEPARATOR . $entry;

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

    private function collectFilesFromDir(string $dir): array
    {
        $files = [];
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($rii as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();
                $files[$filename] = file_get_contents($file->getPathname());
            }
        }

        return $files;
    }
}
