<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Model\Object;

use Neusta\Pimcore\ImportExportBundle\Model\Element;
use Pimcore\Model\Property;

class DataObject extends Element
{
    public string $className;
    public bool $published = false;
    /** @var array<string, Property> */
    public array $fields = []; // flexible set of property values
    /** @var array<string, array<string, Property>> */
    public array $localizedFields = []; // flexible set of localized property values
    /** @var array<string, Property> */
    public array $relations = []; // flexible set of relations (assets, objects, documents)
}
