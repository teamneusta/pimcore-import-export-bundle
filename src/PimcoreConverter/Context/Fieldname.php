<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\PimcoreConverter\Context;

class Fieldname implements \Stringable
{
    public function __construct(
        public readonly string $value,
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
