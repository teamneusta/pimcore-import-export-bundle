<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Model\Document;

class DocumentLink extends Document
{
    public ?string $internal = null;
    public ?string $internalType = null;
    public ?string $direct = null;
    public ?string $linkType = null;
    public ?string $href = null;
}
