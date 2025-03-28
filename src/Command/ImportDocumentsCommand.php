<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Command\Base\AbstractImportBaseCommand;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Pimcore\Model\Document;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @extends AbstractImportBaseCommand<Document>
 */
#[AsCommand(
    name: 'neusta:pimcore:import:documents',
    description: 'Import documents given by file'
)]
class ImportDocumentsCommand extends AbstractImportBaseCommand
{
    /**
     * @param Importer<\ArrayObject<int|string, mixed>, Document> $importer
     */
    public function __construct(
        Importer $importer,
    ) {
        parent::__construct($importer);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Import Pimcore Documents given by file');

        $this->io->writeln('Start importing documents from file');
        $this->io->newLine();

        $filename = $input->getOption('input');
        if (empty($filename)) {
            $this->io->error('No input file specified. Use --input option.');

            return Command::FAILURE;
        }

        if (!file_exists($filename)) {
            $this->io->error(\sprintf('Input file "%s" does not exist.', $filename));

            return Command::FAILURE;
        }
        $yamlInput = file_get_contents($filename);
        if (!$yamlInput) {
            $this->io->error('Input file could not be read');

            return Command::FAILURE;
        }

        $format = $input->getOption('format');
        if (empty($format)) {
            $format = pathinfo($filename, \PATHINFO_EXTENSION);
        }

        try {
            $documents = $this->importer->import($yamlInput, $format, !$input->getOption('dry-run'));
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

        $this->io->success(\sprintf('%d Pimcore Documents have been imported successfully', \count($documents)));

        return Command::SUCCESS;
    }
}
