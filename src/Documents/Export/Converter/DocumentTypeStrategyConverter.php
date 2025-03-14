<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Export\Converter;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\Pimcore\ImportExportBundle\Documents\Export\YamlExportPage;
use Pimcore\Model\Document;

/**
 * @implements Converter<Document, YamlExportPage, GenericContext|null>
 */
class DocumentTypeStrategyConverter implements Converter
{
    /**
     * @param array<class-string, Converter<Document, YamlExportPage, GenericContext|null>> $type2ConverterMap
     */
    public function __construct(
        private array $type2ConverterMap,
    ) {
    }

    public function convert(object $source, ?object $ctx = null): object
    {
        if (!\array_key_exists($source::class, $this->type2ConverterMap)) {
            throw new \InvalidArgumentException('No converter found for type ' . $source::class);
        }

        return $this->type2ConverterMap[$source::class]->convert($source, $ctx);
    }
}
