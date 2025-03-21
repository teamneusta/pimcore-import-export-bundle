<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'neusta:pimcore:import:documents',
    description: 'Import documents given by file'
)]
class ImportDocumentsCommand extends AbstractCommand
{
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
                'The name of the input yaml file',
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
                'Perform a dry run without saving the imported documents'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Import Pimcore Documents given by file');

        $this->io->writeln('Start importing documents from file');
        $this->io->newLine();

        $filename = $input->getOption('input');
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
