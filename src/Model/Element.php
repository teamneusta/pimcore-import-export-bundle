<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Model;

use Neusta\Pimcore\ImportExportBundle\Model\Tag\Tag;

class Element
{
    public const ELEMENTS = 'elements';

    public ?int $id; // important not to set default value here to avoid exporting null values automatically
    public ?int $parentId; // important not to set default value here to avoid exporting null values automatically
    public string $type = 'element';
    public string $path = '';
    public string $key = '';
    /** @var array<Tag> */
    public array $tags; // important not to set default value here to avoid exporting empty arrays automatically
}
