<?php declare(strict_types=1);

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
     *
     * @param array<class-string<TSource>, Converter<TSource, TTarget, GenericContext|null> > $typeToConverterMap
     */
    public function __construct(
        private readonly array $typeToConverterMap,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * Exports one or more Pimcore Elements in the given format (yaml, json, ...)).
     *
     * @param iterable<AbstractElement> $elements
     * @param array<string, mixed>      $ctxParams
     *
     * @throws ConverterException
     */
    public function export(iterable $elements, string $format, array $ctxParams = []): string
    {
        $ctx = new GenericContext();
        foreach ($ctxParams as $key => $value) {
            $ctx->setValue($key, $value);
        }

        $yamlExportElements = [];
        foreach ($elements as $element) {
            foreach (array_keys($this->typeToConverterMap) as $type) {
                if ($element instanceof $type) {
                    $yamlExportElements[] = [$type => $this->typeToConverterMap[$type]->convert($element, $ctx)];
                    continue 2;
                }
            }
        }

        return $this->serializer->serialize([Element::ELEMENTS => $yamlExportElements], $format);
    }
}
