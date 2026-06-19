<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Element;
use Neusta\Pimcore\ImportExportBundle\Model\Object\DataObject;
use Pimcore\Model\Asset as PimcoreAsset;
use Pimcore\Model\DataObject as PimcoreDataObject;
use Pimcore\Model\Document as PimcoreDocument;
use Pimcore\Model\Element\AbstractElement;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @implements Populator<PimcoreDataObject, DataObject, object|null>
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
     * @param PimcoreDataObject $source
     * @param DataObject        $target
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if (!$source instanceof PimcoreDataObject\Concrete) {
            return;
        }

        foreach ($source->getClass()->getFieldDefinitions() as $fieldName => $definition) {
            $value = $this->propertyAccessor->getValue($source, $fieldName);
            if ($value instanceof AbstractElement) {
                if (null === $value->getId()) {
                    $target->relations[$fieldName] = [AbstractElement::class => null];
                    continue;
                }

                $type = match (true) {
                    $value instanceof PimcoreDataObject => PimcoreDataObject::class,
                    $value instanceof PimcoreAsset => PimcoreAsset::class,
                    $value instanceof PimcoreDocument => PimcoreDocument::class,
                    default => $value::class,
                };

                $target->relations[$fieldName] = [$type => $this->typeToConverterMap[$type]->convert($value, $ctx)];
            }
        }
    }
}
