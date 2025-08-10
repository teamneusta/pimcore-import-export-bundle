<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\AssetRepository;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DataObjectRepository;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DocumentRepository;
use Pimcore\Model\Document as PimcoreDocument;
use Psr\Log\LoggerInterface;

/**
 * @implements Populator<\ArrayObject<string, mixed>, PimcoreDocument, GenericContext|null>
 */
class PageImportPopulator implements Populator
{
    public function __construct(
        private readonly AssetRepository $assetRepository,
        private readonly DataObjectRepository $objectRepository,
        private readonly DocumentRepository $documentRepository,
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
        if ('page' === $source['type'] && isset($source['title'])) {
            $target->setTitle($source['title']);
        }

        foreach ($source['properties'] ?? [] as $property) {
            if ($property['value'] && 'asset' === $property['type']) {
                $value = $this->assetRepository->getByPath($property['value']);
            } elseif ($property['value'] && 'document' === $property['type']) {
                $value = $this->documentRepository->getByPath($property['value']);
            } elseif ($property['value'] && 'object' === $property['type']) {
                $value = $this->objectRepository->getByPath($property['value']);
            } else {
                $value = $property['value'];
            }
            $target->setProperty($property['key'], $property['type'], $value);
        }

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
                $target->setRawEditable((string) $key, $editable['type'], $editable['data']);
            }
        }
    }
}
