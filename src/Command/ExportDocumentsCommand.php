<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Command\Base\AbstractExportBaseCommand;
use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ExportRepositoryInterface;
use Pimcore\Model\Document;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @extends AbstractExportBaseCommand<Document>
 */
#[AsCommand(
    name: 'neusta:pimcore:export:documents',
    description: 'Export all Pimcore Documents in one single file'
)]
class ExportDocumentsCommand extends AbstractExportBaseCommand
{
    protected static $defaultName = 'neusta:pimcore:export:documents';

    public function __construct(
        ExportRepositoryInterface $repository,
        Exporter $exporter,
    ) {
        parent::__construct(
            $repository,
            $exporter,
            ['yaml', 'json'],
            Document::class,
        );
    }

    protected function configure(): void
    {
        parent::configure();

        $formatsList = implode(', ', $this->supportedFormats);

        $this
            ->setDescription('Exports documents from the system.')
            ->setHelp(
                <<<HELP
                The <info>%command.name%</info> command exports documents.

                Usage:

                  <info>php %command.full_name%</info>

                Currently supported formats:
                  $formatsList

                Options:
                  --format      Specify the export format (e.g. --format=json)
                  --output      full path filename where exported file will be stored

                Example:
                  php %command.full_name% --format=json --output=/your/path

                Example Result (in yaml format):
                    elements:
                        -
                            Pimcore\Model\Document:
                                published: true
                                navigation_name: Testseite
                                navigation_title: ~
                                title: Testseite
                                controller: 'App\Controller\MyController::indexAction'
                                editables:
                                    main:
                                        type: areablock
                                        name: main
                                        data: [...]

                                id: 4
                                parentId: 2
                                type: page
                                path: /seiten/
                                language: 'de'
                                key: 'Meine Testseite'
                HELP
            );
    }

    protected function exportInFile(array $allElements, InputInterface $input): bool
    {
        $yamlContent = $this->exporter->export($allElements, $input->getOption('format'), ['includeIds' => $input->getOption('includeIds')]);

        $exportFilename = $input->getOption('output');
        // Validate filename to prevent directory traversal
        $safeFilename = basename($exportFilename);
        if ($safeFilename !== $exportFilename) {
            $this->io->warning(\sprintf(
                'For security reasons, path traversal is not recommended. Using "%s" instead of "%s".',
                $safeFilename,
                $exportFilename
            ));
            $exportFilename = $safeFilename;
        }

        $this->io->writeln('Write in file <' . $exportFilename . '>');
        $this->io->newLine();
        if (false === file_put_contents($exportFilename, $yamlContent)) {
            $this->io->error('An error occurred while writing the file');

            return false;
        }

        return true;
    }
}
