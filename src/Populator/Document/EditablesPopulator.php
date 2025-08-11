<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator\Document;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Pimcore\Model\Document as PimcoreDocument;
use Psr\Log\LoggerInterface;

/**
 * @implements Populator<\ArrayObject<string, mixed>, PimcoreDocument, GenericContext|null>
 */
class EditablesPopulator implements Populator
{
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
