<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Model;

class Element
{
    public const ELEMENTS = 'elements';

    public ?int $id = null;
    public int $parentId = 0;
    public string $type = 'element';
    public string $path = '';
    public string $language = '';
    public string $key = '';
}
