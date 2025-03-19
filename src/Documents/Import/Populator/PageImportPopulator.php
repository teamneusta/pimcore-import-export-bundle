<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Import\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Documents\Model\Page;
use Pimcore\Model\Document\Page as PimcorePage;

/**
 * @implements Populator<Page, PimcorePage, GenericContext|null>
 */
class PageImportPopulator implements Populator
{
    /**
     * @param Page                $source
     * @param PimcorePage         $target
     * @param GenericContext|null $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if (property_exists($source, 'language') && isset($source->language)) {
            $target->setProperty('language', 'text', $source->language);
        }
        $target->setProperty('navigation_title', 'text', $source->navigation_title);
        $target->setProperty('navigation_name', 'text', $source->navigation_name);
        /** @var array<string, mixed> $editable */
        foreach ($source->editables ?? [] as $key => $editable) {
            $target->setRawEditable((string) $key, $editable['type'], $editable['data']);
        }
    }
}
