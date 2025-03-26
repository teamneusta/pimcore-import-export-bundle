<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use ArrayObject;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Object\DataObject;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @implements Populator<ArrayObject<int|string, mixed>>, Concrete, GenericContext|null>
 */
class DataObjectImportPopulator implements Populator
{
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        ?PropertyAccessorInterface $propertyAccessor = null
    )
    {
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param DataObject $source
     * @param Concrete $target
     * @param GenericContext|null         $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if ($source->offsetExists('fields') && \is_array($source['fields'])) {
            foreach ($source['fields'] as $fieldName => $fieldValue) {
                $this->propertyAccessor->setValue($target, $fieldName, $fieldValue);
            }
        }
    }
}
