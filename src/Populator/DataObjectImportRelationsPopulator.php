<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ExportRepositoryInterface;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ImportRepositoryInterface;
use Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyObjectRelation;
use Pimcore\Model\DataObject\ClassDefinition\Data\AdvancedManyToManyRelation;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Data\ElementMetadata;
use Pimcore\Model\Element\AbstractElement;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Imports the relation fields of a data object.
 *
 * A relation reference is resolved by its ID if the export contained one. Otherwise the
 * related element is resolved by its path and key, which allows re-importing relation
 * data into a system with different IDs.
 *
 * @implements Populator<\ArrayObject<int|string, mixed>, Concrete, GenericContext|null>
 */
class DataObjectImportRelationsPopulator implements Populator
{
    private PropertyAccessorInterface $propertyAccessor;

    /**
     * @param array<string, ExportRepositoryInterface<AbstractElement>&ImportRepositoryInterface<AbstractElement>> $type2RepositoryMap
     */
    public function __construct(
        private array $type2RepositoryMap = [],
        ?PropertyAccessorInterface $propertyAccessor = null,
        private readonly ?LoggerInterface $logger = null,
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
        if (!$source->offsetExists('relations') || !\is_array($source['relations'])) {
            return;
        }

        foreach ($source['relations'] as $fieldName => $relationValue) {
            if (!\is_array($relationValue)) {
                continue;
            }

            $isCollection = $this->isCollection($relationValue);
            $references = $isCollection ? $relationValue : [$relationValue];

            $relatedElements = [];
            $metadataList = [];
            foreach ($references as $reference) {
                if (!\is_array($reference)) {
                    continue;
                }

                $relatedElement = $this->resolveReference($reference, $fieldName);
                if (null === $relatedElement) {
                    continue;
                }

                $relatedElements[] = $relatedElement;
                $metadataList[] = $reference['metadata'] ?? [];
            }

            if ([] === $relatedElements) {
                continue;
            }

            $this->propertyAccessor->setValue(
                $target,
                $fieldName,
                $isCollection
                    ? $this->createCollectionValue($target, $fieldName, $relatedElements, $metadataList)
                    : $relatedElements[0],
            );
        }
    }

    /**
     * A single relation is exported as a map containing the element class as key.
     * A relation collection is exported as a list of those maps.
     *
     * @param array<array-key, mixed> $relationValue
     */
    private function isCollection(array $relationValue): bool
    {
        return array_is_list($relationValue);
    }

    /**
     * @param array<array-key, mixed> $reference
     */
    private function resolveReference(array $reference, string $fieldName): ?AbstractElement
    {
        foreach ($reference as $elementClass => $elementData) {
            if (!\is_string($elementClass) || !\is_array($elementData)) {
                continue;
            }

            $repository = $this->type2RepositoryMap[$elementClass] ?? null;
            if (null === $repository) {
                continue;
            }

            if (!empty($elementData['id'])) {
                return $repository->getById((int) $elementData['id']);
            }

            $path = $elementData['path'] ?? null;
            $key = $elementData['key'] ?? null;
            if (!\is_string($path) || !\is_string($key)) {
                continue;
            }

            $element = $repository->getByPath($this->buildPath($path, $key));
            if (null === $element) {
                $this->logger?->warning(
                    'Could not resolve related element by path for field "{field}", relation will be skipped.',
                    ['field' => $fieldName, 'path' => $this->buildPath($path, $key)],
                );
            }

            return $element;
        }

        return null;
    }

    private function buildPath(string $path, string $key): string
    {
        return rtrim($path, '/') . '/' . ltrim($key, '/');
    }

    /**
     * @param array<AbstractElement>      $relatedElements
     * @param array<array<string, mixed>> $metadataList
     *
     * @return array<AbstractElement|ElementMetadata>
     */
    private function createCollectionValue(Concrete $target, string $fieldName, array $relatedElements, array $metadataList): array
    {
        $definition = $target->getClass()->getFieldDefinition($fieldName);

        if (!$definition instanceof AdvancedManyToManyRelation && !$definition instanceof AdvancedManyToManyObjectRelation) {
            return $relatedElements;
        }

        $values = [];
        foreach ($relatedElements as $index => $relatedElement) {
            $metadata = new ElementMetadata($fieldName, $definition->getColumnKeys(), $relatedElement);
            $metadata->setData($metadataList[$index]);
            $values[] = $metadata;
        }

        return $values;
    }
}
