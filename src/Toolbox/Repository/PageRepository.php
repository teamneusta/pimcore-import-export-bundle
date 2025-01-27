<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Document;
use Pimcore\Model\Document\Page;

/**
 * @method Page             create(int $parentId, array $data = [], bool $save = true)
 * @method Page|null        getById(int $id, array $params = [])
 * @method Page|null        getByPath(string $path)
 * @method Document\Listing getList(array $config = [])
 * @method void             setHideUnpublished(bool $hideUnpublished)
 * @method bool             doHideUnpublished()
 */
class PageRepository extends AbstractElementRepository
{
    public function __construct()
    {
        parent::__construct(Page::class);
    }
}
