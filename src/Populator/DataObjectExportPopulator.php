<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Object\DataObject;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @implements Populator<Concrete, DataObject, GenericContext|null>
 */
class DataObjectExportPopulator implements Populator
{
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        ?PropertyAccessorInterface $propertyAccessor = null,
    ) {
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param Concrete            $source
     * @param DataObject          $target
     * @param GenericContext|null $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        foreach ($source->getClass()->getFieldDefinitions() as $fieldName => $definition) {
            $value = $this->propertyAccessor->getValue($source, $fieldName);
            $target->fields[$fieldName] = $value;
        }
    }
}
