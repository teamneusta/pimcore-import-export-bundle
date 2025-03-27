<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Command\Base\AbstractExportBaseCommand;
use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ExportRepositoryInterface;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Document;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @extends AbstractExportBaseCommand<Concrete>
 */
#[AsCommand(
    name: 'neusta:pimcore:export:objects',
    description: 'Export all Pimcore Data Objects in one single file'
)]
class ExportDataObjectsCommand extends AbstractExportBaseCommand
{
    public function __construct(
        ExportRepositoryInterface $repository,
        Exporter $exporter,
    ) {
        parent::__construct(
            $repository,
            $exporter,
            ['yaml', 'json'],
            Concrete::class,
        );
    }

    protected function exportInFile(array $allElements, InputInterface $input): bool
    {
        $yamlContent = $this->exporter->export($allElements, $input->getOption('format'));

        $exportFilename = $input->getOption('output');
        // Validate filename to prevent directory traversal
        $safeFilename = basename($exportFilename);
        if ($safeFilename !== $exportFilename) {
            $this->io->warning(\sprintf(
                'For security reasons, path traversal is not allowed. Using "%s" instead of "%s".',
                $safeFilename,
                $exportFilename
            ));
            $exportFilename = $safeFilename;
        }

        $this->io->writeln('Write in file <' . $exportFilename . '>');
        $this->io->newLine();
        if (!file_put_contents($exportFilename, $yamlContent)) {
            $this->io->error('An error occurred while writing the file');

            return false;
        }

        return true;
    }
}
