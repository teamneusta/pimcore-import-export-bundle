<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\AbstractElement;

/**
 * @method DataObject\Listing getList(array $config = [])
 * @method void               setGetInheritedValues(bool $getInheritedValues)
 * @method bool               doGetInheritedValues(Concrete $object = null)
 * @method void               setHideUnpublished(bool $hideUnpublished)
 * @method bool               getHideUnpublished()
 * @method bool               doHideUnpublished()
 *
 * @implements ImportRepositoryInterface<DataObject>
 * @implements ExportRepositoryInterface<DataObject>
 */
class DataObjectRepository extends AbstractElementRepository implements ImportRepositoryInterface, ExportRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(DataObject::class);
    }

    /**
     * @param DataObject $root
     *
     * @return iterable<DataObject>
     */
    public function findAllInTree(AbstractElement $root): iterable
    {
        yield $root;

        foreach ($root->getChildren(includingUnpublished: true) as $child) {
            if ($child instanceof DataObject) {
                yield from $this->findAllInTree($child);
            }
        }
    }

    public function getByPath(string $path): ?DataObject
    {
        $element = parent::getByPath($path);
        if ($element instanceof DataObject) {
            return $element;
        }

        return null;
    }

    public function getById(int $id): ?DataObject
    {
        $element = parent::getById($id);
        if ($element instanceof DataObject) {
            return $element;
        }

        return null;
    }
}
