<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator\Document;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Toolbox\ElementReferenceResolver;
use Pimcore\Model\Document as PimcoreDocument;
use Psr\Log\LoggerInterface;

/**
 * Imports the editables of a document.
 *
 * Relation editables (relation/relations) are resolved by ID if the exported data
 * contains one, otherwise by the type, path and key of the related element.
 *
 * @implements Populator<\ArrayObject<string, mixed>, PimcoreDocument, GenericContext|null>
 */
class EditablesPopulator implements Populator
{
    private const RELATION_EDITABLE_TYPES = ['relation', 'relations'];

    public function __construct(
        private readonly ElementReferenceResolver $elementReferenceResolver,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @param \ArrayObject<string, mixed> $source
     * @param PimcoreDocument             $target
     * @param GenericContext|null         $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if ($target instanceof PimcoreDocument\PageSnippet) {
            /** @var array{type: string, data: mixed} $editable */
            foreach ($source['editables'] ?? [] as $key => $editable) {
                if (!isset($editable['data'])) {
                    $this->logger?->warning('Skipping editable with missing required fields', [
                        'key' => $key,
                        'editable' => $editable,
                    ]);
                    continue; // Skip editables with missing required fields
                }
                $target->setRawEditable((string) $key, $editable['type'], $this->resolveData($editable));
            }
        }
    }

    /**
     * @param array{type: string, data: mixed} $editable
     */
    private function resolveData(array $editable): mixed
    {
        if (!\in_array($editable['type'], self::RELATION_EDITABLE_TYPES, true)) {
            return $editable['data'];
        }

        if ('relations' === $editable['type']) {
            return $this->resolveRelationCollection($editable['data']);
        }

        return $this->resolveRelation($editable['data']);
    }

    private function resolveRelation(mixed $data): mixed
    {
        if (!\is_array($data)) {
            return $data;
        }

        $element = $this->elementReferenceResolver->resolve($data);
        if (null === $element) {
            $this->logger?->warning('Could not resolve related element for relation editable', ['data' => $data]);

            return null;
        }

        return [
            'id' => $element->getId(),
            'type' => $this->elementTypeOf($data),
            'subtype' => $element->getType(),
        ];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function resolveRelationCollection(mixed $data): array
    {
        if (!\is_array($data)) {
            return [];
        }

        $resolved = [];
        foreach ($data as $reference) {
            if (!\is_array($reference)) {
                continue;
            }

            $element = $this->elementReferenceResolver->resolve($reference);
            if (null === $element) {
                $this->logger?->warning('Could not resolve related element for relations editable', ['data' => $reference]);

                continue;
            }

            $resolved[] = [
                'id' => $element->getId(),
                'type' => $this->elementTypeOf($reference),
            ];
        }

        return $resolved;
    }

    /**
     * @param array<string, mixed> $reference
     */
    private function elementTypeOf(array $reference): ?string
    {
        return isset($reference['type']) && \is_string($reference['type']) ? $reference['type'] : null;
    }
}
