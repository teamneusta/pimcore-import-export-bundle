<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Localizedfield;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @implements Populator<\ArrayObject<int|string, mixed>, Concrete, GenericContext|null>
 */
class DataObjectImportFieldsPopulator implements Populator
{
    private PropertyAccessorInterface $propertyAccessor;

    public function __construct(
        ?PropertyAccessorInterface $propertyAccessor = null,
    ) {
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param \ArrayObject<int|string, mixed> $source
     * @param Concrete                        $target
     * @param GenericContext|null             $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if ($source->offsetExists('fields') && \is_array($source['fields'])) {
            foreach ($source['fields'] as $fieldName => $fieldValue) {
                if ('localizedfields' === $fieldName && \is_array($fieldValue)) {
                    $localizedfields = new Localizedfield();
                    $localizedfields->setItems($fieldValue);
                    // Pimcore expects owner/class context on Localizedfield for field-definition lookups.
                    $localizedfields->setObject($target);
                    $localizedfields->setContext([]);
                    $this->propertyAccessor->setValue($target, $fieldName, $localizedfields);
                    continue;
                }

                if (!\is_array($fieldValue)) {
                    $this->propertyAccessor->setValue($target, $fieldName, $fieldValue);
                }
            }
        }
    }
}
