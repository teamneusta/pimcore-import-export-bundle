<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\Registry;

use Pimcore\Model\Element\Tag;

class PendingTagsRegistry
{
    /** @var array<string, Tag[]> */
    private array $pending = [];

    /**
     * @param Tag[] $tags
     */
    public function register(string $fullPath, array $tags): void
    {
        $this->pending[$fullPath] = $tags;
    }

    /**
     * Returns and removes the registered tags for the given full path.
     *
     * @return Tag[]
     */
    public function consume(string $fullPath): array
    {
        $tags = $this->pending[$fullPath] ?? [];
        unset($this->pending[$fullPath]);

        return $tags;
    }
}
