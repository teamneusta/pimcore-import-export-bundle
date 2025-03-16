<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Export\Converter;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\Pimcore\ImportExportBundle\Documents\Model\YamlPage;
use Pimcore\Model\Document;

/**
 * @implements Converter<Document, YamlPage, GenericContext|null>
 */
class DocumentTypeStrategyConverter implements Converter
{
    /**
     * @param array<class-string, Converter<Document, YamlPage, GenericContext|null>> $typeToConverterMap
     */
    public function __construct(
        private array $typeToConverterMap,
    ) {
    }

    public function convert(object $source, ?object $ctx = null): object
    {
        if (!\array_key_exists($source::class, $this->typeToConverterMap)) {
            throw new \InvalidArgumentException('No converter found for type ' . $source::class);
        }

        return $this->typeToConverterMap[$source::class]->convert($source, $ctx);
    }
}
