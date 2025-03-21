<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Documents\Export\PageExporter;
use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\PageRepository;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Document;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'neusta:pimcore:export:pages',
    description: 'Export all pages in one single YAML file'
)]
class ExportPagesCommand extends AbstractCommand
{
    public function __construct(
        private PageRepository $pageRepository,
        private Exporter $pageExporter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_OPTIONAL,
                'The name of the output file (default: export_all_pages.yaml)',
                'export_all_pages.yaml'
            )
            ->addArgument(
                'ids',
                InputArgument::IS_ARRAY,
                'List of page IDs to export'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Export all pages into one single YAML file');

        $pageIds = $input->getArgument('ids');
        $allPages = [];

        if ($pageIds) {
            $ids = array_map('intval', $pageIds);
            foreach ($ids as $id) {
                $page = $this->pageRepository->getById($id);
                if ($page) {
                    $allPages = $this->addPages($page, $allPages);
                } else {
                    $this->io->error("Page with ID $id not found");

                    return Command::FAILURE;
                }
            }
        } else {
            $rootPage = $this->pageRepository->getById(1);
            if (!$rootPage) {
                $this->io->error('Root page (ID: 1) not found');

                return Command::FAILURE;
            }
            $allPages = $this->addPages($rootPage, []);
        }

        $this->io->writeln(\sprintf('Start exporting %d pages', \count($allPages)));
        $this->io->newLine();
        $yamlContent = $this->pageExporter->export($allPages, 'yaml');

        $exportFilename = $input->getOption('output');
        // Validate filename to prevent directory traversal
        $safeFilename = basename($exportFilename);
        if ($safeFilename !== $exportFilename) {
            $this->io->warning(sprintf(
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

            return Command::FAILURE;
        }

        $this->io->success('All pages have been exported successfully');

        return Command::SUCCESS;
    }

    /**
     * @param array<Document> $allPages
     *
     * @return array<Document>
     */
    private function addPages(Document $rootPage, array $allPages): array
    {
        $allPages[] = $rootPage;
        foreach ($rootPage->getChildren(true) as $childPage) {
            if ($childPage instanceof Document) {
                $allPages = $this->addPages($childPage, $allPages);
            }
        }

        return $allPages;
    }
}
