<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Converter;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Pimcore\Model\DataObject\Concrete;

/**
 * @template TSource of object
 * @template TTarget of Concrete
 * @implements SupportsAwareConverterInterface<TSource, TTarget, GenericContext|null>
 */
class SupportsAwareGenericConverter implements SupportsAwareConverterInterface
{
    /**
     * @param class-string $type
     * @param Converter $converter
     */
    public function __construct(
        private string $type,
        private Converter $converter,
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function convert(object $source, ?object $ctx = null): object
    {
        return $this->converter->convert($source, $ctx);
    }

    public function supports(object $source, ?object $ctx = null): bool
    {
        return "Pimcore\Model\DataObject\\" . $source['className'] === $this->type;
    }
}
