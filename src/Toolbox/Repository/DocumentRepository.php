<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Document;

/**
 * @method Document         create(int $parentId, array $data = [], bool $save = true)
 * @method Document|null    getById(int $id, array $params = [])
 * @method Document|null    getByPath(string $path)
 * @method Document\Listing getList(array $config = [])
 * @method void             setHideUnpublished(bool $hideUnpublished)
 * @method bool             doHideUnpublished()
 */
class DocumentRepository extends AbstractElementRepository
{
    public function __construct()
    {
        parent::__construct(Document::class);
    }
}
