<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Export;

use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Converter\Context\ConverterContext;
use Neusta\Pimcore\ImportExportBundle\Converter\TypeStrategyConverter;
use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportEvent;
use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportStatus;
use Neusta\Pimcore\ImportExportBundle\Model\Element;
use Neusta\Pimcore\ImportExportBundle\Serializer\SerializerInterface;
use Pimcore\Model\Element\AbstractElement;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @template TSource of AbstractElement
 * @template TTarget of Element
 */
class Exporter
{
    /**
     * @param TypeStrategyConverter<TSource, TTarget, ConverterContext|null> $typeStrategyConverter
     */
    public function __construct(
        private readonly TypeStrategyConverter $typeStrategyConverter,
        private readonly SerializerInterface $serializer,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * Exports one or more Pimcore Elements in the given format (yaml, json, ...)).
     *
     * @param iterable<TSource>    $elements
     * @param array<string, mixed> $ctxParams
     *
     * @throws ConverterException
     */
    public function export(iterable $elements, string $format, array $ctxParams = []): string
    {
        $ctx = ConverterContext::create();
        foreach ($ctxParams as $key => $value) {
            $ctx->setValue($key, $value);
        }

        $yamlExportElements = [];
        foreach ($elements as $element) {
            $fqcn = get_class($element);
            $yamlContent = null;
            try {
                $yamlContent = $this->typeStrategyConverter->convert($element, $ctx);
            } catch (ConverterException $e) {
                $this->dispatcher->dispatch(new ImportEvent(ImportStatus::Failed, $fqcn, [], $element, null, $e->getMessage()));
            }
            $yamlExportElements[] = [$fqcn => $yamlContent];
        }

        return $this->serializer->serialize([Element::ELEMENTS => $yamlExportElements], $format);
    }
}
