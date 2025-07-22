<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command\Base;

use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ExportRepositoryInterface;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\AbstractElement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @template TElement of AbstractElement
 */
abstract class AbstractExportBaseCommand extends AbstractCommand
{
    /**
     * @param class-string                        $elementType
     * @param array<string>                       $supportedFormats
     * @param ExportRepositoryInterface<TElement> $repository
     */
    public function __construct(
        protected ExportRepositoryInterface $repository,
        protected Exporter $exporter,
        protected array $supportedFormats,
        protected string $elementType = AbstractElement::class,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'include-ids',
                null,
                InputOption::VALUE_NONE,
                'If set, the export will include asset/document/object IDs and ParentIDs - be aware with re-importing'
            )
            ->addOption(
                'output',
                'o',
                InputOption::VALUE_OPTIONAL,
                'The name of the output file (default: export_all.yaml)',
                'export_all.yaml'
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
        $this->io->title('Export ' . $this->elementType . ' into one single file');

        $elementIds = $input->getArgument('ids');
        if ([] === $elementIds) {
            $elementIds = [1];
        }

        // check if format is supported
        if (!\in_array($input->getOption('format'), $this->supportedFormats, true)) {
            $this->io->error('Unsupported format: ' . $input->getOption('format') . '. Supported formats are: ' . implode(', ', $this->supportedFormats));

            return Command::FAILURE;
        }

        $allElements = [];
        // collect all elements
        $ids = array_map('intval', $elementIds);
        foreach ($ids as $id) {
            $element = $this->repository->getById($id);
            if ($element) {
                $allElements = $this->addElements($element, $allElements);
            } else {
                $this->io->error("$this->elementType with ID $id not found");

                return Command::FAILURE;
            }
        }

        $this->io->writeln(\sprintf('Start exporting %d ' . $this->elementType, \count($allElements)));
        $this->io->newLine();

        if (!$this->exportInFile($allElements, $input)) {
            return Command::FAILURE;
        }

        $this->io->success('All ' . $this->elementType . ' have been exported successfully');

        return Command::SUCCESS;
    }

    /**
     * @param array<TElement> $allElements
     * @param TElement        $rootElement
     *
     * @return array<TElement>
     */
    private function addElements(AbstractElement $rootElement, array $allElements): array
    {
        $allElements[] = $rootElement;
        if (
            $rootElement instanceof Asset
            || $rootElement instanceof Document
            || $rootElement instanceof AbstractObject
        ) {
            foreach ($rootElement->getChildren(true) as $childElement) {
                if ($childElement instanceof $this->elementType) {
                    $allElements = $this->addElements($childElement, $allElements); // @phpstan-ignore-line
                }
            }
        }

        return $allElements;
    }

    /**
     * @param array<TElement> $allElements
     *
     * @throws \Neusta\ConverterBundle\Exception\ConverterException
     */
    abstract protected function exportInFile(array $allElements, InputInterface $input): bool;
}
