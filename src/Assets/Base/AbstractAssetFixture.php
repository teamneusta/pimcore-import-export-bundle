<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Assets\Base;

use Neusta\Pimcore\FixtureBundle\Fixture\AbstractFixture;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\AssetRepository;
use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image;

abstract class AbstractAssetFixture extends AbstractFixture
{
    public function __construct(
        protected AssetRepository $assetRepository,
        protected string $fullQualifiedFilename,
        protected string $pimcoreFilename,
        protected string $pimcoreBasePath,
        protected string $fixtureAssetMarker,
    ) {
    }

    public function create(): void
    {
        $asset = new Image();
        $asset->setFilename($this->pimcoreFilename);
        $asset->setData(file_get_contents($this->fullQualifiedFilename));
        $asset->setParent(Asset::getByPath($this->pimcoreBasePath));

        $this->replaceIfExists($asset);

        $this->addReference($this->fixtureAssetMarker, $asset);
    }

    private function replaceIfExists(Image $asset): void
    {
        $oldAsset = $this->assetRepository->getByPath($this->pimcoreBasePath . $asset->getFullPath());
        if (null !== $oldAsset) {
            $oldAsset->delete();
        }

        $asset->save();
    }
}
