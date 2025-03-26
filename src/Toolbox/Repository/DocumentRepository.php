<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Document;
use Pimcore\Model\Element\AbstractElement;

/**
 * @method Document         create(int $parentId, array $data = [], bool $save = true)
 * @method Document|null    getById(int $id, array $params = [])
 * @method Document\Listing getList(array $config = [])
 * @method void             setHideUnpublished(bool $hideUnpublished)
 * @method bool             doHideUnpublished()
 *
 * @implements ImportRepositoryInterface<Document>
 */
class DocumentRepository extends AbstractElementRepository implements ImportRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Document::class);
    }

    /**
     * @return iterable<Document>
     */
    public function findAllDocsWithChildren(Document $page): iterable
    {
        yield $page;

        foreach ($page->getChildren(true) as $child) {
            if ($child instanceof Document) {
                yield from $this->findAllDocsWithChildren($child);
            }
        }
    }

    /**
     * @param string $path
     * @return Document|null
     */
    public function getByPath(string $path): ?AbstractElement
    {
        return parent::getByPath($path);
    }

}
