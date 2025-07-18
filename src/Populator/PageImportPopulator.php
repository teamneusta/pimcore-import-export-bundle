<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Pimcore\Model\Document as PimcoreDocument;
use Psr\Log\LoggerInterface;

/**
 * @implements Populator<\ArrayObject<string, mixed>, PimcoreDocument, GenericContext|null>
 */
class PageImportPopulator implements Populator
{
    private const TEXT_PROPERTIES = [
        'language',
        'navigation_title',
        'navigation_name',
        // Add more properties here if necessary
    ];

    public function __construct(
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
        if ($target instanceof PimcoreDocument) {
            foreach (self::TEXT_PROPERTIES as $property) {
                if (isset($source[$property])) {
                    $target->setProperty($property, 'text', $source[$property]);
                }
            }

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
