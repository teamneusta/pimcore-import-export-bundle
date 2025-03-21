<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DocumentRepository;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Document;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'neusta:pimcore:export:documents',
    description: 'Export all Pimcore documents in one single file'
)]
class ExportDocumentsCommand extends AbstractCommand
{
    public function __construct(
        private DocumentRepository $repository,
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
                'The name of the output file (default: export_all_documents.yaml)',
                'export_all_documents.yaml'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'The format of the output file (default: yaml): yaml, json',
                'yaml'
            )
            ->addArgument(
                'ids',
                InputArgument::IS_ARRAY,
                'List of document IDs to export'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Export Pimcore Documents into one single file');

        $documentIds = $input->getArgument('ids');
        $allDocuments = [];

        if ($documentIds) {
            $ids = array_map('intval', $documentIds);
            foreach ($ids as $id) {
                $document = $this->repository->getById($id);
                if ($document) {
                    $allDocuments = $this->addDocuments($document, $allDocuments);
                } else {
                    $this->io->error("Page with ID $id not found");

                    return Command::FAILURE;
                }
            }
        } else {
            $rootDocument = $this->repository->getById(1);
            if (!$rootDocument) {
                $this->io->error('Root document (ID: 1) not found');

                return Command::FAILURE;
            }
            $allDocuments = $this->addDocuments($rootDocument, []);
        }

        $this->io->writeln(\sprintf('Start exporting %d Pimcore Documents', \count($allDocuments)));
        $this->io->newLine();
        $yamlContent = $this->pageExporter->export($allDocuments, $input->getOption('format'));

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

            return Command::FAILURE;
        }

        $this->io->success('All Pimcore Documents have been exported successfully');

        return Command::SUCCESS;
    }

    /**
     * @param array<Document> $allDocuments
     *
     * @return array<Document>
     */
    private function addDocuments(Document $rootPage, array $allDocuments): array
    {
        $allDocuments[] = $rootPage;
        foreach ($rootPage->getChildren(true) as $childPage) {
            if ($childPage instanceof Document) {
                $allDocuments = $this->addDocuments($childPage, $allDocuments);
            }
        }

        return $allDocuments;
    }
}
