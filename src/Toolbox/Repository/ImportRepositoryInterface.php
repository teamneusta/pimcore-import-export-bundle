<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Element\AbstractElement;

/**
 * @template TElement of AbstractElement
 */
interface ImportRepositoryInterface
{
    /**
     * @return TElement
     */
    public function getByPath(string $path): ?AbstractElement;
}
