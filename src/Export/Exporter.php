<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Export;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Model\Element;
use Neusta\Pimcore\ImportExportBundle\Serializer\SerializerInterface;
use Pimcore\Model\Element\AbstractElement;

class Exporter
{
    /**
     * @template TSource of AbstractElement
     * @template TTarget of Element
     * @param array<class-string<TSource>, Converter<TSource, TTarget, GenericContext|null> > $typeToConverterMap
     */
    public function __construct(
        private readonly array               $typeToConverterMap,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * Exports one or more pages in the given format (yaml, json, ...)).
     *
     * @param iterable<AbstractElement> $assets
     *
     * @throws ConverterException
     */
    public function export(iterable $assets, string $format): string
    {
        $yamlExportElements = [];
        foreach ($assets as $asset) {
            foreach (array_keys($this->typeToConverterMap) as $type) {
                if ($asset instanceof $type) {
                    $yamlExportElements[] = [$type => $this->typeToConverterMap[$type]->convert($asset)];
                    continue 2;
                }
            }
        }

        return $this->serializer->serialize([Element::ELEMENTS => $yamlExportElements], $format);
    }
}
