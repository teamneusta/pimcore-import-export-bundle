<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Converter;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;

/**
 * @template TSource of object
 * @template TTarget of object
 * @template TContext of GenericContext|null
 *
 * @implements Converter<TSource, TTarget, TContext>
 */
class TypeStrategyConverter implements Converter
{
    /**
     * @param array<class-string, Converter<TSource, TTarget, TContext>> $typeToConverterMap
     */
    public function __construct(
        private array $typeToConverterMap,
    ) {
    }

    public function convert(object $source, ?object $ctx = null): object
    {
        foreach (array_keys($this->typeToConverterMap) as $type) {
            if ($source instanceof $type) {
                return $this->typeToConverterMap[$type]->convert($source, $ctx);
            }
        }
        throw new \InvalidArgumentException('No converter found for type ' . $source::class);
    }
}
