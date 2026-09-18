<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository;

use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Element\Tag;

class TagRepository
{
    public const CTYPE_ASSET = 'asset';
    public const CTYPE_DOCUMENT = 'document';
    public const CTYPE_DATAOBJECT = 'object';

    public function getById(int $id): ?Tag
    {
        return Tag::getById($id);
    }

    public function getByPath(string $path): ?Tag
    {
        return Tag::getByPath($path);
    }

    /**
     * @return Tag[]
     */
    public function getTagsForAsset(int $assetId): array
    {
        return $this->getTagsForElement(self::CTYPE_ASSET, $assetId);
    }

    /**
     * @return Tag[]
     */
    public function getTagsForDocument(int $documentId): array
    {
        return $this->getTagsForElement(self::CTYPE_DOCUMENT, $documentId);
    }

    /**
     * @return Tag[]
     */
    public function getTagsForObject(int $objectId): array
    {
        return $this->getTagsForElement(self::CTYPE_DATAOBJECT, $objectId);
    }

    /**
     * @return Tag[]
     */
    public function getTagsForElement(string $cType, int $cId): array
    {
        return Tag::getTagsForElement($cType, $cId);
    }

    public function addTagToAsset(int $cId, Tag $tag): void
    {
        Tag::addTagToElement(self::CTYPE_ASSET, $cId, $tag);
    }

    public function addTagToDocument(int $cId, Tag $tag): void
    {
        Tag::addTagToElement(self::CTYPE_DOCUMENT, $cId, $tag);
    }

    public function addTagToObject(int $cId, Tag $tag): void
    {
        Tag::addTagToElement(self::CTYPE_DATAOBJECT, $cId, $tag);
    }

    public function addTagToElement(string $cType, int $cId, Tag $tag): void
    {
        Tag::addTagToElement($cType, $cId, $tag);
    }

    public function removeTagFromElement(string $cType, int $cId, Tag $tag): void
    {
        Tag::removeTagFromElement($cType, $cId, $tag);
    }

    /**
     * @param Tag[] $tags
     */
    public function setTagsForElement(string $cType, int $cId, array $tags): void
    {
        Tag::setTagsForElement($cType, $cId, $tags);
    }

    /**
     * @param int[] $cIds
     * @param int[] $tagIds
     */
    public function batchAssignTagsToElementIds(string $cType, array $cIds, array $tagIds, bool $replace = false): void
    {
        Tag::batchAssignTagsToElement($cType, $cIds, $tagIds, $replace);
    }

    /**
     * @param iterable<AbstractElement> $objects
     * @param iterable<int>             $tagIds
     */
    public function batchAssignTagsToElements(iterable $objects, iterable $tagIds, bool $replace = false): void
    {
        foreach ($this->filter($objects) as $object) {
            $this->batchAssignTagsToElementIds($object->getType(), [$object->getId()], iterator_to_array($tagIds), $replace); // @phpstan-ignore-line
        }
    }

    /**
     * @param string[]       $subtypes
     * @param class-string[] $classNames
     *
     * @return AbstractElement[]
     */
    public function getElementsForTag(
        Tag $tag,
        string $type,
        array $subtypes = [],
        array $classNames = [],
        bool $considerChildTags = false,
    ): array {
        return Tag::getElementsForTag($tag, $type, $subtypes, $classNames, $considerChildTags);
    }

    /**
     * @param iterable<AbstractElement> $objects
     *
     * @return iterable<AbstractElement>
     */
    private function filter(iterable $objects): iterable
    {
        foreach ($objects as $object) {
            if ($object->getId() && \in_array($object->getType(), [self::CTYPE_ASSET, self::CTYPE_DOCUMENT, self::CTYPE_DATAOBJECT], true)) {
                yield $object;
            }
        }
    }
}
