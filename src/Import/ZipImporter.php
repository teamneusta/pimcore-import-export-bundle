<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ZipImporter
{
    /** @var string */
    private const ZIPIMPORT_DIR_PREFIX = 'zipimport_';
    private Filesystem $filesystem;
    private string $tempDir;

    public function __construct(
        ?string $tempDir = null,
    ) {
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
        if (false === $yamlData) {
            throw new \RuntimeException("Failed to read YAML file: $yamlPath");
        }

        $assets = $this->collectAssetDirectories($extractPath);

        return array_merge(['yaml' => $yamlData], $assets);
    }

    private function createTempExtractPath(): string
    {
        $extractPath = $this->tempDir . '/' . self::ZIPIMPORT_DIR_PREFIX . uniqid();
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
     * @return array<string, array<string, \SplFileInfo>>
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

            $files = $this->collectFileInfosFromDir($fullPath);
            if (!empty($files)) {
                $assets[$entry] = $files;
            }
        }

        return $assets;
    }

    /**
     * @return array<string, \SplFileInfo>
     */
    private function collectFileInfosFromDir(string $dir): array
    {
        $files = [];
        $rii = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($rii as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();
                $files[$filename] = new \SplFileInfo($file->getPathname());
            }
        }

        return $files;
    }

    public function cleanUp(): void
    {
        $finder = new Finder();
        $finder->directories()
            ->in($this->tempDir)
            ->name(self::ZIPIMPORT_DIR_PREFIX . '*');

        foreach ($finder as $dir) {
            $this->filesystem->remove($dir->getRealPath());
        }
    }
}
