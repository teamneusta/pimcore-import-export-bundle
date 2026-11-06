<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Element;
use Neusta\Pimcore\ImportExportBundle\Model\Object\DataObject;
use Pimcore\Model\DataObject as PimcoreDataObject;
use Pimcore\Model\DataObject\ClassDefinition\Data\Relations\AbstractRelations;
use Pimcore\Model\DataObject\Data\ElementMetadata;
use Pimcore\Model\Element\AbstractElement;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Exports all relation fields of a data object.
 *
 * Every relation is exported as a reference which always contains the type as well as
 * path and key of the related element. Thus a relation can be restored without knowing
 * any IDs. Only if the context contains "includeIds" => true, the ID (and parent ID) of
 * the related element is exported in addition.
 *
 * A single relation is exported as one reference, a collection relation as a list of
 * references. Advanced relation entries additionally contain their metadata columns.
 *
 * @implements Populator<PimcoreDataObject, DataObject, GenericContext|null>
 */
class DataObjectExportRelationsPopulator implements Populator
{
    private PropertyAccessorInterface $propertyAccessor;

    /**
     * @template TSource of AbstractElement
     * @template TTarget of Element
     *
     * @param array<class-string<TSource>, Converter<TSource, TTarget, GenericContext|null> > $typeToConverterMap
     */
    public function __construct(
        ?PropertyAccessorInterface $propertyAccessor,
        private readonly array $typeToConverterMap,
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
            if (!$definition instanceof AbstractRelations) {
                continue;
            }

            $value = $this->propertyAccessor->getValue($source, $fieldName);

            if ($value instanceof AbstractElement) {
                if (null !== $relation = $this->convertElement($value, $ctx)) {
                    $target->relations[$fieldName] = $relation;
                }

                continue;
            }

            if (!\is_array($value)) {
                continue;
            }

            $relations = [];
            foreach ($value as $relationItem) {
                $relatedElement = $relationItem instanceof ElementMetadata ? $relationItem->getElement() : $relationItem;
                if (!$relatedElement instanceof AbstractElement) {
                    continue 2;
                }

                $relation = $this->convertElement($relatedElement, $ctx);
                if (null === $relation) {
                    continue 2;
                }

                if ($relationItem instanceof ElementMetadata && [] !== $relationItem->getData()) {
                    $relation['metadata'] = $relationItem->getData();
                }

                $relations[] = $relation;
            }

            $target->relations[$fieldName] = $relations;
        }
    }

    /**
     * @return array<class-string, Element>|null
     */
    private function convertElement(AbstractElement $element, ?GenericContext $ctx): ?array
    {
        foreach (array_keys($this->typeToConverterMap) as $type) {
            if ($element instanceof $type) {
                return [$type => $this->typeToConverterMap[$type]->convert($element, $ctx)];
            }
        }

        return null;
    }
}
