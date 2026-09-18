<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator\Tag;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Tag\Tag;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\TagRepository;
use Pimcore\Model\Element\Tag as PimcoreTag;

/**
 * @implements Populator<PimcoreTag, Tag ,GenericContext|null>
 */
class TagPopulator implements Populator
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {
    }

    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if ($ctx?->hasKey('includeIds') && true === $ctx->getValue('includeIds')) {
            $target->id = $source->getId();
            $target->parentId = $source->getParentId();
            $target->path = $source->getIdPath();
        } else {
            $target->path = $this->resolveTagPathWithTagNames($source->getIdPath());
        }
    }

    private function resolveTagPathWithTagNames(string $idPath): string
    {
        $ids = array_filter(explode('/', trim($idPath, '/')));
        $keys = [];

        foreach ($ids as $id) {
            $tag = $this->tagRepository->getById((int) $id);
            if (null === $tag) {
                continue;
            }

            $keys[] = $tag->getName();
        }

        $concatenatedKeys = implode('/', $keys);
        if (!empty($concatenatedKeys)) {
            $concatenatedKeys .= '/';
        }

        return '/' . $concatenatedKeys;
    }
}
