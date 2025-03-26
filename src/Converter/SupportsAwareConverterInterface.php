<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Converter;

use Neusta\ConverterBundle\Converter;

/**
 * @template TSource of object
 * @template TTarget of object
 * @template TContext of object|null
 *
 * @extends Converter<TSource, TTarget, TContext>
 */
interface SupportsAwareConverterInterface extends Converter
{
    public function supports(object $source, ?object $ctx = null): bool;
}
