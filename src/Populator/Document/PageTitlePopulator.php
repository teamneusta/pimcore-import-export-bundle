<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator\Document;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Pimcore\Model\Document as PimcoreDocument;

/**
 * @implements Populator<\ArrayObject<string, mixed>, PimcoreDocument, GenericContext|null>
 */
class PageTitlePopulator implements Populator
{
    /**
     * @param \ArrayObject<string, mixed> $source
     * @param PimcoreDocument             $target
     * @param GenericContext|null         $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if ($target instanceof PimcoreDocument\Page && isset($source['title'])) {
            $target->setTitle($source['title']);
        }
    }
}
