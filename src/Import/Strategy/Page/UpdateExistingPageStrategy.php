<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\Strategy\Page;

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
     * @param Document\PageSnippet $oldElement
     * @param Document\PageSnippet $newElement
     *
     * @throws DuplicateFullPathException
     */
    public function mergeAndSave(AbstractElement $oldElement, AbstractElement $newElement): void
    {
        $oldElement->setPublished($newElement->getPublished());
        $oldElement->setController($newElement->getController());

        if ($oldElement instanceof Document\Page && $newElement instanceof Document\Page) {
            $oldElement->setTitle($newElement->getTitle());
        }

        $oldElement->setProperty('language', 'text', $newElement->getProperty('language'));
        $oldElement->setProperty('navigation_name', 'text', $newElement->getProperty('navigation_name'));
        $oldElement->setProperty('navigation_title', 'text', $newElement->getProperty('navigation_title'));
        $oldElement->setEditables($newElement->getEditables());
        $oldElement->save(['versionNote' => 'overwritten by pimcore-import-export-bundle']);
    }
}
