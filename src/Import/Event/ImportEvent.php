<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\Event;

use Pimcore\Model\Element\AbstractElement;

class ImportEvent
{
    /**
     * @param array<string, mixed> $yamlContent
     */
    public function __construct(
        protected readonly ImportStatus $status,
        protected readonly string $type,
        protected readonly array $yamlContent,
        protected readonly ?AbstractElement $newElement,
        protected readonly ?AbstractElement $oldElement,
    ) {
    }

    public function getStatus(): ImportStatus
    {
        return $this->status;
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function getYamlContent(): array
    {
        return $this->yamlContent;
    }

    public function getNewElement(): ?AbstractElement
    {
        return $this->newElement;
    }

    public function getOldElement(): ?AbstractElement
    {
        return $this->oldElement;
    }
}
