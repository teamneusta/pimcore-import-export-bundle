<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\AbstractElement;

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

    /**
     * @param string $path
     * @return AbstractElement|null
     */
    public function getByPath(string $path): ?AbstractElement
    {
        return parent::getByPath($path);
    }

}
