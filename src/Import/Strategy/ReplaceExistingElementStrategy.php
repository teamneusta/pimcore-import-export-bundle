<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\Strategy\Page;

use Neusta\Pimcore\ImportExportBundle\Import\Strategy\MergeElementStrategy;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Element\DuplicateFullPathException;

/**
 * @implements MergeElementStrategy<AbstractElement>
 */
class ReplaceExistingElementStrategy implements MergeElementStrategy
{
    /**
     * @throws DuplicateFullPathException
     */
    public function mergeAndSave(AbstractElement $oldElement, AbstractElement $newElement): void
    {
        $oldElement->delete();
        $newElement->save();
    }
}
