<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Command\Base\AbstractExportBaseCommand;
use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Export\Service\ZipService;
use Neusta\Pimcore\ImportExportBundle\Model\Asset\Asset;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ExportRepositoryInterface;
use Pimcore\Model\Asset as PimcoreAsset;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @extends AbstractExportBaseCommand<PimcoreAsset, Asset>
 */
#[AsCommand(
    name: 'neusta:pimcore:export:assets',
    description: 'Export all Pimcore Assets into a ZIP file'
)]
class ExportAssetsCommand extends AbstractExportBaseCommand
{
    protected static $defaultName = 'neusta:pimcore:export:assets';

    public function __construct(
        ExportRepositoryInterface $repository,
        Exporter $exporter,
        private ZipService $zipService,
    ) {
        parent::__construct(
            $repository,
            $exporter,
            ['yaml', 'json'],
            Asset::class,
        );
    }

    protected function configure(): void
    {
        parent::configure();

        $formatsList = implode(', ', $this->supportedFormats);

        $this
            ->setDescription('Exports assets from the system.')
            ->setHelp(
                <<<HELP
                The <info>%command.name%</info> command exports assets.

                The output file will be a ZIP file containing all the assets incl. the exported yaml or json file.

                Usage:

                  <info>php %command.full_name%</info>

                Currently supported formats:
                  $formatsList

                Options:
                  --format      Specify the export format (e.g. --format=json)
                  --output      full path filename where exported file will be stored

                Example:
                  php %command.full_name% --format=json --output=/your/path

                Example Result:
                    elements:
                        -
                            Pimcore\Model\Asset:
                                filename: instagram.png
                                id: 7
                                parentId: 6
                                type: image
                                path: /Icons/
                                language: ''
                                key: instagram.png
                HELP
            );
    }

    protected function exportInFile(array $allElements, InputInterface $input): bool
    {
        $yamlContent = $this->exporter->export($allElements, $input->getOption('format'), ['include-ids' => $input->getOption('includeIds')]);

        $zipFilename = $input->getOption('output');
        try {
            $this->zipService->createZipWithAssets($allElements, $yamlContent, $zipFilename);
        } catch (\RuntimeException $e) {
            $this->io->error('An error occurred while creating the ZIP file: ' . $e->getMessage());

            return false;
        }

        return true;
    }
}
