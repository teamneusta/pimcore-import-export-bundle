<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Element;
use Neusta\Pimcore\ImportExportBundle\Model\Tag\Tag as TagModel;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\TagRepository;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Element\Tag;

/**
 * @implements Populator<AbstractElement, Element, GenericContext|null>
 */
class AssignedTagsPopulator implements Populator
{
    /**
     * @param Converter<Tag, TagModel, GenericContext|null> $tagConverter
     */
    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly Converter $tagConverter,
    ) {
    }

    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if (!$source->getId()) {
            return;
        }

        $cType = '';
        match (true) {
            $source instanceof Asset => $cType = TagRepository::CTYPE_ASSET,
            $source instanceof Document => $cType = TagRepository::CTYPE_DOCUMENT,
            $source instanceof AbstractObject => $cType = TagRepository::CTYPE_DATAOBJECT,
            default => $cType = 'unknown',
        };

        if ('unknown' !== $cType) {
            $tags = $this->tagRepository->getTagsForElement($cType, $source->getId());
            if (empty($tags)) {
                return;
            }
            $target->tags = array_map(
                fn (Tag $tag) => $this->tagConverter->convert($tag, $ctx),
                $tags,
            );
        }
    }
}
