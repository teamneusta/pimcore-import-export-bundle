<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use ArrayObject;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Pimcore\Model\Document as PimcoreDocument;

/**
 * @implements Populator<ArrayObject<string, mixed>, PimcoreDocument, GenericContext|null>
 */
class PageImportPopulator implements Populator
{
    /**
     * @param \ArrayObject<string, mixed> $source
     * @param PimcoreDocument             $target
     * @param GenericContext|null         $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if ($target instanceof PimcoreDocument\PageSnippet) {
            if ($source->offsetExists('language') && isset($source['language'])) {
                $target->setProperty('language', 'text', $source['language']);
            }
            if ($source->offsetExists('navigation_title') && isset($source['navigation_title'])) {
                $target->setProperty('navigation_title', 'text', $source['navigation_title']);
            }
            if ($source->offsetExists('navigation_name') && isset($source['navigation_name'])) {
                $target->setProperty('navigation_name', 'text', $source['navigation_name']);
            }

            /** @var array<string, mixed> $editable */
            foreach ($source['editables'] ?? [] as $key => $editable) {
                $target->setRawEditable((string) $key, $editable['type'], $editable['data']);
            }
        }
    }
}
