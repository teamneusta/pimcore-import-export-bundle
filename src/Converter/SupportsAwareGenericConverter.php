<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Converter;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Import\Factory\ContextBaseTargetFactory;
use Pimcore\Model\DataObject\Concrete;

/**
 * @template TTarget of Concrete
 *
 * @implements SupportsAwareConverterInterface<\ArrayObject<int|string, mixed>, TTarget, GenericContext|null>
 */
class SupportsAwareGenericConverter implements SupportsAwareConverterInterface
{
    /**
     * @param class-string $type
     * @param Converter<\ArrayObject<int|string, mixed>, TTarget, GenericContext|null> $converter
     */
    public function __construct(
        private string    $type,
        private Converter $converter,
    )
    {
    }

    /**
     * @param \ArrayObject<int|string, mixed> $source
     * @param GenericContext|null $ctx
     *
     * @return TTarget
     *
     * @throws ConverterException
     */
    public function convert(object $source, ?object $ctx = null): object
    {
        if (!$ctx) {
            $ctx = new GenericContext();
        }
        if ($source->offsetExists('className')) {
            $ctx->setValue(ContextBaseTargetFactory::TARGET_TYPE, "Pimcore\Model\DataObject\\" . $source['className']);
        }
        return $this->converter->convert($source, $ctx);
    }

    /**
     * @param \ArrayObject<int|string, mixed> $source
     * @param GenericContext|null $ctx
     */
    public function supports(object $source, ?object $ctx = null): bool
    {
        return $source->offsetExists('className')
            && "Pimcore\Model\DataObject\\" . $source['className'] instanceof $this->type;
    }
}
