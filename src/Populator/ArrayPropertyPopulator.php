<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Element;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @template TSource of \ArrayObject
 * @template TTarget of Element
 * @template TContext of GenericContext|null
 *
 * @implements Populator<TSource, TTarget, TContext>
 */
class ArrayPropertyPopulator implements Populator
{
    private PropertyAccessorInterface $propertyAccessor;

    private string $sourceArrayKey;

    public function __construct(
        private string $targetProperty,
        private mixed $defaultValue = null,
        ?string $sourceArrayKey = null,
        ?PropertyAccessorInterface $propertyAccessor = null,
    ) {
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
        $this->sourceArrayKey = $sourceArrayKey ?? $this->targetProperty;
    }

    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if ($source->offsetExists($this->sourceArrayKey) && isset($source[$this->sourceArrayKey])) {
            $this->propertyAccessor->setValue($target, $this->targetProperty, $source[$this->sourceArrayKey]);
        } else {
            $this->propertyAccessor->setValue($target, $this->targetProperty, $this->defaultValue);
        }
    }
}
