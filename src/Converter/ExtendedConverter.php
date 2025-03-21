<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Converter;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Pimcore\Model\Element\AbstractElement;

/**
 * @template TTarget of object
 * @template TSource of AbstractElement
 * @template TContext of GenericContext|null
 * @implements Converter<TSource, TTarget, TContext>
 */
class ExtendedConverter implements Converter
{
    /**
     * @param Converter<TSource, TTarget, TContext> $converter
     * @param array<Populator<TSource, TTarget, TContext>> $postPopulators
     */
    public function __construct(
        private Converter $converter,
        private array     $postPopulators,
    ) {
    }

    /**
     * @param TSource  $source
     * @param TContext $ctx
     *
     * @return TTarget
     */
    public function convert(object $source, ?object $ctx = null): object
    {
        $target = $this->converter->convert($source, $ctx);

        foreach ($this->postPopulators as $populator) {
            $populator->populate($target, $source, $ctx);
        }

        return $target;
    }
}
