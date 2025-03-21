<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Asset;
use Pimcore\Model\Element\DuplicateFullPathException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'neusta:pimcore:import:assets',
    description: 'Import assets from a ZIP file'
)]
class ImportAssetsCommand extends AbstractCommand
{
    /**
     * @param Importer<\ArrayObject<int|string, mixed>, Asset> $importer
     */
    public function __construct(
        private Importer $importer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'input',
                'i',
                InputOption::VALUE_REQUIRED,
                'The name of the input ZIP file',
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'The format of the input file: yaml, json',
                ''
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Perform a dry run without saving the imported assets'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Import Pimcore Assets from ZIP file');

        $this->io->writeln('Start importing assets from ZIP file');
        $this->io->newLine();

        $filename = $input->getOption('input');
        $zip = new \ZipArchive();
        if (true !== $zip->open($filename)) {
            $this->io->error('Input ZIP file could not be opened');

            return Command::FAILURE;
        }

        $extractPath = sys_get_temp_dir() . '/pimcore_assets_import';
        $zip->extractTo($extractPath);
        $zip->close();

        $files = glob($extractPath . '/*');
        if (!$files) {
            $this->io->error('There are no extracted files');

            return Command::FAILURE;
        }

        $assetsFile = array_values(array_filter($files, fn ($file) => is_file($file)))[0];
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
            if (file_exists($extractPath . '/' . $asset->getType() . '/' . $asset->getFilename())) {
                $fileContent = file_get_contents($extractPath . '/' . $asset->getType() . '/' . $asset->getFilename());
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
}
