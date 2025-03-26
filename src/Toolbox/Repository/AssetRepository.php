<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Asset;

/**
 * @method Asset         create(int $parentId, array $data = [], bool $save = true)
 * @method Asset|null    getById(int $id, array $params = [])
 * @method Asset\Listing getList(array $config = [])
 *
 * @implements ImportRepositoryInterface<Asset>
 */
class AssetRepository extends AbstractElementRepository implements ImportRepositoryInterface
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

    public function getByPath(string $path): ?Asset
    {
        $element = parent::getByPath($path);
        if ($element instanceof Asset) {
            return $element;
        }

        return null;
    }
}
