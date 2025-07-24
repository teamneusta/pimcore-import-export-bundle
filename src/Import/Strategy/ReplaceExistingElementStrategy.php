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
        try {
            $oldElement->delete();
            $newElement->save();
        } catch (\Exception $e) {
            // If save fails after delete, we're in an inconsistent state
            throw new \RuntimeException('Failed to replace element: ' . $e->getMessage(), 0, $e);
        }
    }
}
