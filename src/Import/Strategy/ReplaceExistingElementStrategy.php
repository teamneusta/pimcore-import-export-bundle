<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\Strategy;

use Pimcore\Model\Element\AbstractElement;

/**
 * @implements MergeElementStrategy<AbstractElement>
 */
class ReplaceExistingElementStrategy implements MergeElementStrategy
{
    /**
     * @throws \RuntimeException
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
