<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command\Base;

use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Element\AbstractElement;
use Symfony\Component\Console\Input\InputOption;

/**
 * @template TElement of AbstractElement
 */
class AbstractImportBaseCommand extends AbstractCommand
{
    /**
     * @param Importer<\ArrayObject<int|string, mixed>, TElement> $importer
     */
    public function __construct(
        protected Importer $importer,
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
}
