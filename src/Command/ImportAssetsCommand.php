<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Command\Base\AbstractImportBaseCommand;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Pimcore\Model\Asset;
use Pimcore\Model\Element\DuplicateFullPathException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @extends AbstractImportBaseCommand<Asset>
 */
#[AsCommand(
    name: 'neusta:pimcore:import:assets',
    description: 'Import assets from a ZIP file'
)]
class ImportAssetsCommand extends AbstractImportBaseCommand
{
    /**
     * @param Importer<\ArrayObject<int|string, mixed>, Asset> $importer
     */
    public function __construct(
        private string $extractPath,
        Importer $importer,
    ) {
        parent::__construct($importer);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Import Pimcore Assets from ZIP file');

        $this->io->writeln('Start importing assets from ZIP file');
        $this->io->newLine();

        $filename = $input->getOption('input');

        $extension = pathinfo($filename, \PATHINFO_EXTENSION);

        // if file is ZIP
        if ('zip' === $extension) {
            $assetsFile = $this->extractAssetsFileFromZip($filename);
            if (!$assetsFile) {
                $this->io->error('ZIP file could not be extracted');

                return Command::FAILURE;
            }
        } else {
            $assetsFile = $filename;
        }

        $yamlInput = file_get_contents($assetsFile);
        if (!$yamlInput) {
            $this->io->error('Input file could not be read');

            return Command::FAILURE;
        }

        $format = $input->getOption('format');

        if (empty($format)) {
            $format = pathinfo($assetsFile, \PATHINFO_EXTENSION);
        }

        try {
            $assets = $this->importer->import($yamlInput, $format, false);
        } catch (\DomainException $e) {
            $this->io->error(\sprintf('Invalid %s format: %s', $format, $e->getMessage()));

            return Command::FAILURE;
        } catch (\InvalidArgumentException $e) {
            $this->io->error(\sprintf('Import error: %s', $e->getMessage()));

            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->io->error(\sprintf('Unexpected error during import: %s', $e->getMessage()));

            return Command::FAILURE;
        }

        foreach ($assets as $asset) {
            if (file_exists($this->extractPath . '/' . $asset->getType() . '/' . $asset->getFilename())) {
                $fileContent = file_get_contents($this->extractPath . '/' . $asset->getType() . '/' . $asset->getFilename());
                $asset->setData($fileContent);
            }
            if (!$input->getOption('dry-run')) {
                try {
                    $asset->save();
                } catch (DuplicateFullPathException $e) {
                    $this->io->error(\sprintf('Unexpected error during saving asset: %s', $e->getMessage()));

                    return Command::FAILURE;
                }
            }
        }

        $this->io->success(\sprintf('%d Pimcore Assets have been imported successfully', \count($assets)));

        return Command::SUCCESS;
    }

    private function extractAssetsFileFromZip(string $filename): ?string
    {
        $zip = new \ZipArchive();
        if (true !== $zip->open($filename)) {
            $this->io->error('Input ZIP file could not be opened');

            return null;
        }

        $zip->extractTo($this->extractPath);
        $zip->close();

        $files = glob($this->extractPath . '/*');
        if (!$files) {
            $this->io->error('There are no extracted files');

            return null;
        }

        $filteredFiles = array_values(array_filter($files, fn ($file) => is_file($file)));

        return !empty($filteredFiles) ? $filteredFiles[0] : null;
    }
}
