<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox;

use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\AssetRepository;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DataObjectRepository;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DocumentRepository;
use Pimcore\Model\Element\AbstractElement;

/**
 * Resolves references to Pimcore elements.
 *
 * A reference is identified by the ID of the element if it contains one. Otherwise the
 * element is identified by its type, path and key (its "business key"). This allows
 * importing exported data into a system with different element IDs.
 */
class ElementReferenceResolver
{
    public function __construct(
        private readonly AssetRepository $assetRepository,
        private readonly DocumentRepository $documentRepository,
        private readonly DataObjectRepository $dataObjectRepository,
    ) {
    }

    /**
     * @param array{type?: string|null, path?: string|null, key?: string|null, id?: int|string|null} $reference
     */
    public function resolve(array $reference): ?AbstractElement
    {
        $repository = $this->repositoryFor($reference['type'] ?? null);
        if (null === $repository) {
            return null;
        }

        if (!empty($reference['id'])) {
            return $repository->getById((int) $reference['id']);
        }

        $path = $reference['path'] ?? null;
        $key = $reference['key'] ?? null;
        if (!\is_string($path) || !\is_string($key)) {
            return null;
        }

        return $repository->getByPath(rtrim($path, '/') . '/' . ltrim($key, '/'));
    }

    /**
     * @return array{type: string, path: string, key: string}|null
     */
    public function createReference(AbstractElement $element): ?array
    {
        $type = match (true) {
            $element instanceof \Pimcore\Model\Asset => 'asset',
            $element instanceof \Pimcore\Model\Document => 'document',
            $element instanceof \Pimcore\Model\DataObject\AbstractObject => 'object',
            default => null,
        };

        if (null === $type) {
            return null;
        }

        return [
            'type' => $type,
            'path' => (string) $element->getPath(),
            'key' => (string) $element->getKey(),
        ];
    }

    private function repositoryFor(?string $elementType): AssetRepository|DocumentRepository|DataObjectRepository|null
    {
        return match ($elementType) {
            'asset' => $this->assetRepository,
            'document' => $this->documentRepository,
            'object', 'dataObject' => $this->dataObjectRepository,
            default => null,
        };
    }
}
