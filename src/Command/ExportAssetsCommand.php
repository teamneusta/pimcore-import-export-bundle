<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Command;

use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Export\Service\ZipService;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\AssetRepository;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\Asset;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'neusta:pimcore:export:assets',
    description: 'Export all Pimcore assets into a ZIP file'
)]
class ExportAssetsCommand extends AbstractCommand
{
    public function __construct(
        private AssetRepository $repository,
        private Exporter $exporter,
        private ZipService $zipService,
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
                'The name of the output ZIP file (default: export_all_assets.zip)',
                'export_all_assets.zip'
            )
            ->addOption(
                'format',
                'f',
                InputOption::VALUE_OPTIONAL,
                'The format of the metadata file (default: yaml): yaml, json',
                'yaml'
            )
            ->addArgument(
                'ids',
                InputArgument::IS_ARRAY,
                'List of asset IDs to export'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Export Pimcore Assets into a ZIP file');

        $assetIds = $input->getArgument('ids');
        $allAssets = [];

        if ($assetIds) {
            $ids = array_map('intval', $assetIds);
            foreach ($ids as $id) {
                $asset = $this->repository->getById($id);
                if ($asset) {
                    $allAssets = $this->addAssets($asset, $allAssets);
                } else {
                    $this->io->error("Asset with ID $id not found");

                    return Command::FAILURE;
                }
            }
        } else {
            $rootAsset = $this->repository->getById(1);
            if (!$rootAsset) {
                $this->io->error('Root asset (ID: 1) not found');

                return Command::FAILURE;
            }
            $allAssets = $this->addAssets($rootAsset, []);
        }

        $this->io->writeln(\sprintf('Start exporting %d Pimcore Assets', \count($allAssets)));
        $this->io->newLine();
        $yamlContent = $this->exporter->export($allAssets, $input->getOption('format'));

        $zipFilename = $input->getOption('output');
        $this->zipService->createZipWithAssets($allAssets, $yamlContent, $zipFilename);

        $this->io->success('All Pimcore Assets have been exported successfully');

        return Command::SUCCESS;
    }

    /**
     * @param array<Asset> $allAssets
     *
     * @return array<Asset>
     */
    private function addAssets(Asset $rootAsset, array $allAssets): array
    {
        $allAssets[] = $rootAsset;
        foreach ($rootAsset->getChildren() as $childAsset) {
            if ($childAsset instanceof Asset) {
                $allAssets = $this->addAssets($childAsset, $allAssets);
            }
        }

        return $allAssets;
    }
}
