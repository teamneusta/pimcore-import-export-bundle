<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Document;

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
