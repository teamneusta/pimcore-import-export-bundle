<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator\Document;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Toolbox\ElementReferenceResolver;
use Pimcore\Model\Document as PimcoreDocument;

/**
 * Imports the properties of a document.
 *
 * Element properties are resolved by ID if the exported value is an ID, otherwise by
 * path and key.
 *
 * @implements Populator<\ArrayObject<string, mixed>, PimcoreDocument, GenericContext|null>
 */
class PropertiesPopulator implements Populator
{
    private const ELEMENT_PROPERTY_TYPES = ['asset', 'document', 'object'];

    public function __construct(
        private readonly ElementReferenceResolver $elementReferenceResolver,
    ) {
    }

    /**
     * @param \ArrayObject<string, mixed> $source
     * @param PimcoreDocument             $target
     * @param GenericContext|null         $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        foreach ($source['properties'] ?? [] as $property) {
            $target->setProperty($property['key'], $property['type'], $this->resolveValue($property));
        }
    }

    /**
     * @param array{key: string, type: string, value: mixed} $property
     */
    private function resolveValue(array $property): mixed
    {
        $value = $property['value'];
        if (!$value || !\in_array($property['type'], self::ELEMENT_PROPERTY_TYPES, true)) {
            return $value;
        }

        if ($this->isId($value)) {
            return $this->elementReferenceResolver->resolve(['type' => $property['type'], 'id' => $value]);
        }

        return $this->elementReferenceResolver->resolve(['type' => $property['type'], 'path' => '/', 'key' => ltrim((string) $value, '/')]);
    }

    private function isId(mixed $value): bool
    {
        return \is_int($value) || (\is_string($value) && ctype_digit($value));
    }
}
