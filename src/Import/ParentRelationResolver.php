<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Element\ElementInterface;

class ParentRelationResolver
{
    public function resolve(AbstractElement $element): AbstractElement
    {
        $path = $element->getPath();

        if ($parent = $this->isValidParent($element)) {
            // Wenn die parentId gültig ist, den Path aktualisieren
            $element->setPath($this->generatePath($parent, $element));
        } elseif ($path) {
            // Wenn der Pfad existiert, versuchen, die parentId zu ermitteln
            $parent = $this->findParentByPath($path, $element);
            if ($parent) {
                $element->setParentId($parent->getId());
            } else {
                throw new \InvalidArgumentException('Neither parentId nor path leads to a valid parent element');
            }
        } else {
            throw new \InvalidArgumentException('Neither parentId nor path leads to a valid parent element');
        }

        return $element;
    }

    private function isValidParent(AbstractElement $element): ?ElementInterface
    {
        $parentId = $element->getParentId();
        if ($parentId) {
            $parent = $this->getById($parentId, $element);
            if ($this->isCompatibleType($parent, $element)) {
                return $parent;
            }
        }

        return null;
    }

    private function getById(int $id, AbstractElement $element): ?ElementInterface
    {
        if ($element instanceof Document) {
            return Document::getById($id);
        } elseif ($element instanceof Asset) {
            return Asset::getById($id);
        } elseif ($element instanceof DataObject) {
            return DataObject::getById($id);
        }

        return null;
    }

    private function findParentByPath(string $path, AbstractElement $element): ?AbstractElement
    {
        if ($element instanceof Document) {
            return Document::getByPath($path);
        } elseif ($element instanceof Asset) {
            return Asset::getByPath($path);
        } elseif ($element instanceof DataObject) {
            return DataObject::getByPath($path);
        }

        return null;
    }

    private function generatePath(ElementInterface $parent, AbstractElement $element): string
    {
        return rtrim($parent->getPath() ?? '', '/') . '/' . $element->getKey();
    }

    private function isCompatibleType(?ElementInterface $parent, AbstractElement $child): bool
    {
        return
            ($child instanceof Document && $parent instanceof Document)
            || ($child instanceof Asset && $parent instanceof Asset)
            || ($child instanceof DataObject && $parent instanceof DataObject\Folder)
        ;
    }
}
