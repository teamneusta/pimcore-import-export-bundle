<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Asset;
use Pimcore\Model\Element\AbstractElement;

/**
 * @method Asset         create(int $parentId, array $data = [], bool $save = true)
 * @method Asset\Listing getList(array $config = [])
 *
 * @implements ImportRepositoryInterface<Asset>
 * @implements ExportRepositoryInterface<Asset>
 */
class AssetRepository extends AbstractElementRepository implements ImportRepositoryInterface, ExportRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Asset::class);
    }

    /**
     * @param Asset $root
     *
     * @return iterable<Asset>
     */
    public function findAllInTree(AbstractElement $root): iterable
    {
        yield $root;

        foreach ($root->getChildren() as $child) {
            if ($child instanceof Asset) {
                yield from $this->findAllInTree($child);
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

    public function getById(int $id): ?Asset
    {
        $element = parent::getById($id);
        if ($element instanceof Asset) {
            return $element;
        }

        return null;
    }
}
