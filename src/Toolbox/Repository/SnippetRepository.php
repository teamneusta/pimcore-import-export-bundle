<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Document\Listing;
use Pimcore\Model\Document\Snippet;

/**
 * @method Snippet      create(int $parentId, array $data = [], bool $save = true)
 * @method Snippet|null getById(int $id, array $params = [])
 * @method Snippet|null getByPath(string $path, array|bool $force = false)
 * @method Listing      getList(array $config = [])
 * @method void         setHideUnpublished(bool $hideUnpublished)
 * @method bool         doHideUnpublished()
 */
class SnippetRepository extends AbstractElementRepository
{
    public function __construct()
    {
        parent::__construct(Snippet::class);
    }
}
