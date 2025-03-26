<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Converter;

use Neusta\ConverterBundle\Converter;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

/**
 * @template TSource of object
 * @template TTarget of object
 * @template TContext of object|null
 *
 * @implements Converter<TSource, TTarget, TContext>
 */
class ConverterStrategy implements Converter
{
    /**
     * @var iterable<SupportsAwareConverterInterface<TSource, TTarget, TContext>>
     */
    private iterable $converters;

    /**
     * @param iterable<SupportsAwareConverterInterface<TSource, TTarget, TContext>> $converters
     */
    public function __construct(
        #[TaggedIterator('neusta_pimcore_import_export.objects.import.converter', defaultPriorityMethod: 'getPriority')]
        iterable $converters,
    ) {
        $this->converters = $converters;
    }

    public function convert(object $source, ?object $ctx = null): object
    {
        foreach ($this->converters as $converter) {
            if ($converter->supports($source, $ctx)) {
                return $converter->convert($source, $ctx);
            }
        }

        throw new \InvalidArgumentException('No converter found for type ' . $source::class);
    }
}
