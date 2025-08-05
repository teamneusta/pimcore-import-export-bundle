<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\Strategy\Document;

use Neusta\Pimcore\ImportExportBundle\Import\Strategy\MergeElementStrategy;
use Pimcore\Model\Document;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Element\DuplicateFullPathException;

/**
 * @implements MergeElementStrategy<Document\PageSnippet>
 */
class UpdateExistingPageStrategy implements MergeElementStrategy
{
    /**
     * @param Document $oldElement
     * @param Document $newElement
     *
     * @throws DuplicateFullPathException
     * @throws \InvalidArgumentException
     */
    public function mergeAndSave(AbstractElement $oldElement, AbstractElement $newElement): void
    {
        $oldElement->setPublished($newElement->getPublished());

        if ($oldElement instanceof Document\PageSnippet && $newElement instanceof Document\PageSnippet) {
            $oldElement->setEditables($newElement->getEditables());
            $oldElement->setController($newElement->getController());
        }

        if ($oldElement instanceof Document\Page && $newElement instanceof Document\Page) {
            $oldElement->setTitle($newElement->getTitle());
        }
        foreach ($newElement->getProperties() as $property) {
            $name = $property->getName();
            $type = $property->getType();
            if (null === $name || null === $type) {
                continue; // Skip invalid properties
            }
            $oldElement->setProperty($name, $type, $property->getData());
        }
        $oldElement->save(['versionNote' => 'overwritten by pimcore-import-export-bundle']);
    }
}
