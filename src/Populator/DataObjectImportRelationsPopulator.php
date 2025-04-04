<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ExportRepositoryInterface;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\AbstractElement;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @implements Populator<\ArrayObject<int|string, mixed>, Concrete, GenericContext|null>
 */
class DataObjectImportRelationsPopulator implements Populator
{
    private PropertyAccessorInterface $propertyAccessor;

    /**
     * @param array<string, ExportRepositoryInterface<AbstractElement>> $type2RepositoryMap
     */
    public function __construct(
        private array $type2RepositoryMap = [],
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
        if ($source->offsetExists('relations') && \is_array($source['relations'])) {
            foreach ($source['relations'] as $fieldName => $fieldValue) {
                foreach ($fieldValue as $type => $relation) {
                    if (\array_key_exists('id', $relation)) {
                        $relatedElement = $this->type2RepositoryMap[$type]->getById($relation['id']);
                        $this->propertyAccessor->setValue($target, $fieldName, $relatedElement);
                    }
                }
            }
        }
    }
}
