<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Document\Document;
use Pimcore\Model\Document\Page as PimcorePage;

/**
 * @implements Populator<PimcorePage, PimcorePage, GenericContext|null>
 */
class PageImportPopulator implements Populator
{
    /**
     * @param Document            $source
     * @param PimcorePage         $target
     * @param GenericContext|null $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
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
