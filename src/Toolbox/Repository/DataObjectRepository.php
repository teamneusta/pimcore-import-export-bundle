<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Concrete;

/**
 * @method DataObject\Listing getList(array $config = [])
 * @method void               setGetInheritedValues(bool $getInheritedValues)
 * @method bool               doGetInheritedValues(Concrete $object = null)
 * @method void               setHideUnpublished(bool $hideUnpublished)
 * @method bool               getHideUnpublished()
 * @method bool               doHideUnpublished()
 *
 * @implements ImportRepositoryInterface<Concrete>
 * @implements ExportRepositoryInterface<Concrete>
 */
class DataObjectRepository extends AbstractElementRepository implements ImportRepositoryInterface, ExportRepositoryInterface
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

    public function getById(int $id): ?Concrete
    {
        $element = parent::getById($id);
        if ($element instanceof Concrete) {
            return $element;
        }

        return null;
    }
}
