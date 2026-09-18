<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Import\Registry\PendingTagsRegistry;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\TagRepository;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Element\Tag as PimcoreTag;

/**
 * Resolves tags from imported YAML data and registers them for post-save assignment.
 *
 * Tags with an `id` are looked up by ID (must exist).
 * Tags without an `id` are found or created by their name path (`path` + `key`).
 *
 * @implements Populator<\ArrayObject<string, mixed>, AbstractElement, GenericContext|null>
 */
class ImportTagsPopulator implements Populator
{
    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly PendingTagsRegistry $registry,
    ) {
    }

    /**
     * @param \ArrayObject<string, mixed> $source
     * @param AbstractElement             $target
     * @param GenericContext|null         $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        $tagsData = $source['tags'] ?? [];
        if (empty($tagsData)) {
            return;
        }

        $tags = [];
        foreach ($tagsData as $tagData) {
            $tag = $this->resolveTag($tagData);
            if (null !== $tag) {
                $tags[] = $tag;
            }
        }

        if (!empty($tags)) {
            $this->registry->register($target->getFullPath(), $tags);
        }
    }

    /**
     * @param array<string, mixed> $tagData
     */
    private function resolveTag(array $tagData): ?PimcoreTag
    {
        if (!empty($tagData['id']) && $tagData['id'] > 0) {
            return $this->tagRepository->getById((int) $tagData['id']);
        }

        $path = $tagData['path'] ?? '/';
        $key = $tagData['key'] ?? '';

        if ('' === $key) {
            return null;
        }

        $fullNamePath = rtrim($path, '/') . '/' . $key;

        return $this->findOrCreateTagByNamePath($fullNamePath);
    }

    private function findOrCreateTagByNamePath(string $namePath): ?PimcoreTag
    {
        $segments = array_values(array_filter(explode('/', trim($namePath, '/'))));
        if (empty($segments)) {
            return null;
        }

        $parentId = 0;
        $tag = null;
        $builtPath = '';

        foreach ($segments as $segment) {
            $builtPath .= '/' . $segment;
            $existing = $this->tagRepository->getByPath($builtPath);
            if (null !== $existing) {
                $tag = $existing;
            } else {
                $tag = new PimcoreTag();
                $tag->setName($segment);
                $tag->setParentId($parentId);
                $tag->save();
            }
            $parentId = (int) $tag->getId();
        }

        return $tag;
    }
}
