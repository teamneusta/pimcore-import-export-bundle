<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Document\Editable;
use Neusta\Pimcore\ImportExportBundle\Toolbox\ElementReferenceResolver;
use Pimcore\Model\Document\Editable as PimcoreEditable;
use Pimcore\Model\Element;

/**
 * Exports the data of a document editable.
 *
 * Relation editables (relation/relations) reference other elements. With
 * "includeIds" => true they are exported by their IDs (the Pimcore default), otherwise
 * by the type, path and key of the related elements.
 *
 * @implements Populator<PimcoreEditable, Editable, GenericContext|null>
 */
class EditableDataPopulator implements Populator
{
    public function __construct(
        private readonly ElementReferenceResolver $elementReferenceResolver,
    ) {
    }

    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if (!$source instanceof PimcoreEditable\Relation && !$source instanceof PimcoreEditable\Relations) {
            $target->data = $source->getData();

            return;
        }

        if ($ctx?->hasKey('includeIds') && true === $ctx->getValue('includeIds')) {
            $target->data = $source->getDataForResource();

            return;
        }

        $target->data = $source instanceof PimcoreEditable\Relations
            ? $this->exportRelations($source)
            : $this->exportRelation($source);
    }

    private function exportRelation(PimcoreEditable\Relation $source): mixed
    {
        $data = $source->getData();

        // Keep the original data if the referenced element cannot be resolved,
        // so that no information is lost during export.
        return $this->exportElementReference($data['type'] ?? null, $data['id'] ?? null) ?? $data;
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function exportRelations(PimcoreEditable\Relations $source): array
    {
        $references = [];
        foreach ($source->getElementIds() as $elementId) {
            $reference = \is_array($elementId)
                ? $this->exportElementReference($elementId['type'] ?? null, $elementId['id'] ?? null)
                : null;

            $references[] = $reference ?? $elementId;
        }

        return $references;
    }

    /**
     * @return array{type: string, path: string, key: string}|null
     */
    private function exportElementReference(mixed $elementType, mixed $elementId): ?array
    {
        if (!\is_string($elementType) || !\is_scalar($elementId) || !$elementId) {
            return null;
        }

        $element = Element\Service::getElementById($elementType, (int) $elementId);
        if (!$element instanceof Element\ElementInterface) {
            return null;
        }

        return $this->elementReferenceResolver->createReference($element);
    }
}
