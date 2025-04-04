<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Object\DataObject;
use Pimcore\Model\DataObject as PimcoreDataObject;
use Pimcore\Model\Element\AbstractElement;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @implements Populator<PimcoreDataObject, DataObject, GenericContext|null>
 */
class DataObjectExportFieldsPopulator implements Populator
{
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        ?PropertyAccessorInterface $propertyAccessor = null,
    ) {
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param PimcoreDataObject   $source
     * @param DataObject          $target
     * @param GenericContext|null $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if (!$source instanceof PimcoreDataObject\Concrete) {
            return;
        }

        foreach ($source->getClass()->getFieldDefinitions() as $fieldName => $definition) {
            $value = $this->propertyAccessor->getValue($source, $fieldName);
            if (!$value instanceof AbstractElement) {
                $target->fields[$fieldName] = $value;
            }
        }
    }
}
