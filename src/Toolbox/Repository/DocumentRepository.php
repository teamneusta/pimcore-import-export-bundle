<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Document;
use Pimcore\Model\Element\AbstractElement;

/**
 * @method Document         create(int $parentId, array $data = [], bool $save = true)
 * @method Document\Listing getList(array $config = [])
 * @method void             setHideUnpublished(bool $hideUnpublished)
 * @method bool             doHideUnpublished()
 *
 * @implements ImportRepositoryInterface<Document>
 * @implements ExportRepositoryInterface<Document>
 */
class DocumentRepository extends AbstractElementRepository implements ImportRepositoryInterface, ExportRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Document::class);
    }

    /**
     * @param Document $root
     *
     * @return iterable<Document>
     */
    public function findAllInTree(AbstractElement $root): iterable
    {
        yield $root;

        foreach ($root->getChildren(true) as $child) {
            if ($child instanceof Document) {
                yield from $this->findAllInTree($child);
            }
        }
    }

    public function getByPath(string $path): ?Document
    {
        $element = parent::getByPath($path);
        if ($element instanceof Document) {
            return $element;
        }

        return null;
    }

    public function getById(int $id): ?Document
    {
        $element = parent::getById($id);
        if ($element instanceof Document) {
            return $element;
        }

        return null;
    }
}
