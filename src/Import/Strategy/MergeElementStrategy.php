<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\Strategy;

use Pimcore\Model\Element\AbstractElement;

/**
 * @template TElement of AbstractElement
 */
interface MergeElementStrategy
{
    /**
     * @param TElement $oldElement
     * @param TElement $newElement
     */
    public function mergeAndSave(AbstractElement $oldElement, AbstractElement $newElement): void;
}
