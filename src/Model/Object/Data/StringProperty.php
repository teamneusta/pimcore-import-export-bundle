<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Model\Object\Data;

class StringProperty implements Property
{
    public string $key;
    public string $type;
    public string $value;
    public bool $inherited;
}
