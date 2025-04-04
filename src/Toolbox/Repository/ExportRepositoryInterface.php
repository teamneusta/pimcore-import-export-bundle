<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Element\AbstractElement;

/**
 * @template TElement of AbstractElement
 */
interface ExportRepositoryInterface
{
    /**
     * @return TElement
     */
    public function getById(int $id): ?AbstractElement;

    /**
     * @param TElement $root
     *
     * @return iterable<TElement>
     */
    public function findAllInTree(AbstractElement $root): iterable;
}
