<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Command\Base\AbstractExportBaseCommand;
use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Export\Service\ZipService;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ExportRepositoryInterface;
use Pimcore\Model\Asset;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @extends AbstractExportBaseCommand<Asset>
 */
#[AsCommand(
    name: 'neusta:pimcore:export:assets',
    description: 'Export all Pimcore Assets into a ZIP file'
)]
class ExportAssetsCommand extends AbstractExportBaseCommand
{
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

    protected function exportInFile(array $allElements, InputInterface $input): bool
    {
        $yamlContent = $this->exporter->export($allElements, $input->getOption('format'));

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
