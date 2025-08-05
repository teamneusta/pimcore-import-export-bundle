<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Command\Base\AbstractExportBaseCommand;
use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Model\Object\DataObject;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ExportRepositoryInterface;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @extends AbstractExportBaseCommand<Concrete, DataObject>
 */
#[AsCommand(
    name: 'neusta:pimcore:export:objects',
    description: 'Export all Pimcore Data Objects in one single file'
)]
class ExportDataObjectsCommand extends AbstractExportBaseCommand
{
    protected static $defaultName = 'neusta:pimcore:export:objects';

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

    protected function configure(): void
    {
        parent::configure();

        $formatsList = implode(', ', $this->supportedFormats);

        $this
            ->setDescription('Exports data objects from the system.')
            ->setHelp(
                <<<HELP
                The <info>%command.name%</info> command exports objects.

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
                            Pimcore\Model\DataObject:
                                className: SocialMediaItem
                                published: true
                                fields:
                                    label: Instagram
                                relations:
                                    icon:
                                        Pimcore\Model\Asset: { filename: instagram.png, id: 7, parentId: 6, type: image, path: /Icons/, language: '', key: instagram.png }
                                id: 2
                                parentId: 3
                                type: object
                                path: '/Social Media Items/'
                                language: 'en'
                                key: Instagram
                HELP
            );
    }

    protected function exportInFile(array $allElements, InputInterface $input): bool
    {
        $yamlContent = $this->exporter->export($allElements, $input->getOption('format'), ['include-ids' => $input->getOption('include-ids')]);

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
        if (!file_put_contents($exportFilename, $yamlContent)) {
            $this->io->error('An error occurred while writing the file');

            return false;
        }

        return true;
    }
}
