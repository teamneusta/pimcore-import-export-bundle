<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Documents\Import\PageImporter;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'neusta:pimcore:import:pages',
    description: 'Import pages given by YAML file'
)]
class ImportPagesCommand extends AbstractCommand
{
    public function __construct(
        private PageImporter $pageImporter,
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
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Perform a dry run without saving the imported pages'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Import pages given by YAML file');

        $this->io->writeln('Start importing pages from YAML file');
        $this->io->newLine();

        $yamlInput = file_get_contents($input->getOption('input'));
        if (!$yamlInput) {
            $this->io->error('Input file could not be read');

            return Command::FAILURE;
        }

        $pages = $this->pageImporter->import($yamlInput, 'yaml', !$input->getOption('dry-run'));

        $this->io->success(\sprintf('%d pages have been imported successfully', \count($pages)));

        return Command::SUCCESS;
    }
}
