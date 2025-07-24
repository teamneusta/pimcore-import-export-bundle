<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Model\Document;

class PageSnippet extends Folder
{
    public ?string $controller = null;
    /** @var array<Editable> */
    public array $editables = [];
}
