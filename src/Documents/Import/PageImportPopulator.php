<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Import;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Documents\Export\YamlExportPage;
use Pimcore\Model\Document\Page;

/**
 * @implements Populator<YamlExportPage, Page, GenericContext|null>
 */
class PageImportPopulator implements Populator
{
    /**
     * @param YamlExportPage      $source
     * @param Page                $target
     * @param GenericContext|null $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if (property_exists($source, 'language') && isset($source->language)) {
            $target->setProperty('language', 'text', $source->language);
        }
        $target->setProperty('navigation_title', 'text', $source->title);
        $target->setProperty('navigation_name', 'text', $source->key);
        /** @var array<string, mixed> $editable */
        foreach ($source->editables ?? [] as $key => $editable) {
            $target->setRawEditable($key, $editable['type'], $editable['data']);
        }
    }
}
