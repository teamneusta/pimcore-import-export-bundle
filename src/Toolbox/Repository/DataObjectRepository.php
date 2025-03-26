<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Concrete;

/**
 * @method DataObject|null    getById(int $id, array $params = [])
 * @method DataObject\Listing getList(array $config = [])
 * @method void               setGetInheritedValues(bool $getInheritedValues)
 * @method bool               doGetInheritedValues(Concrete $object = null)
 * @method void               setHideUnpublished(bool $hideUnpublished)
 * @method bool               getHideUnpublished()
 * @method bool               doHideUnpublished()
 *
 * @implements ImportRepositoryInterface<Concrete>
 */
class DataObjectRepository extends AbstractElementRepository implements ImportRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(DataObject::class);
    }

    /**
     * @return iterable<Concrete>
     */
    public function findAllObjectsWithChildren(Concrete $page): iterable
    {
        yield $page;

        foreach ($page->getChildren(includingUnpublished: true) as $child) {
            if ($child instanceof Concrete) {
                yield from $this->findAllObjectsWithChildren($child);
            }
        }
    }

    public function getByPath(string $path): ?Concrete
    {
        $element = parent::getByPath($path);
        if ($element instanceof Concrete) {
            return $element;
        }

        return null;
    }
}
