<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Model\Object;

use Neusta\Pimcore\ImportExportBundle\Model\Element;

class DataObject extends Element
{
    public string $className;
    public bool $published = false;
    /** @var array<string, mixed> */
    public array $fields = []; // flexible set of property values
    /** @var array<string, mixed> */
    public array $relations = []; // flexible set of relations (assets, objects, documents)
}
