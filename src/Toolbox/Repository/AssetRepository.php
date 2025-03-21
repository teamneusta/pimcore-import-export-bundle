<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Asset;

/**
 * @method Asset         create(int $parentId, array $data = [], bool $save = true)
 * @method Asset|null    getById(int $id, array $params = [])
 * @method Asset|null    getByPath(string $path)
 * @method Asset\Listing getList(array $config = [])
 */
class AssetRepository extends AbstractElementRepository
{
    public function __construct()
    {
        parent::__construct(Asset::class);
    }

    /**
     * @return iterable<Asset>
     */
    public function findAllAssetsWithChildren(Asset $asset): iterable
    {
        yield $asset;

        foreach ($asset->getChildren() as $child) {
            if ($child instanceof Asset) {
                yield from $this->findAllAssetsWithChildren($child);
            }
        }
    }
}
