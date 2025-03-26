<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Converter;

use ArrayObject;
use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Pimcore\Model\DataObject\Concrete;

/**
 * @template TTarget of Concrete
 *
 * @implements SupportsAwareConverterInterface<ArrayObject<int|string, mixed>, TTarget, GenericContext|null>
 */
class SupportsAwareGenericConverter implements SupportsAwareConverterInterface
{
    /**
     * @param class-string                                                             $type
     * @param Converter<\ArrayObject<int|string, mixed>, TTarget, GenericContext|null> $converter
     */
    public function __construct(
        private string $type,
        private Converter $converter,
    ) {
    }

    /**
     * @param \ArrayObject<int|string, mixed> $source
     * @param GenericContext|null             $ctx
     *
     * @return TTarget
     *
     * @throws ConverterException
     */
    public function convert(object $source, ?object $ctx = null): object
    {
        return $this->converter->convert($source, $ctx);
    }

    /**
     * @param \ArrayObject<int|string, mixed> $source
     * @param GenericContext|null             $ctx
     */
    public function supports(object $source, ?object $ctx = null): bool
    {
        return $source->offsetExists('className')
            && "Pimcore\Model\DataObject\\" . $source['className'] === $this->type;
    }
}
