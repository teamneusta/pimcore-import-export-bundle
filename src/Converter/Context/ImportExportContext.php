<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Converter\Context;

use Neusta\ConverterBundle\Converter\Context\GenericContext;

class ImportExportContext extends GenericContext
{
    /**
     * @param array<string, mixed> $ctxParams
     */
    public function __construct(
        private array $ctxParams = [],
    ) {
    }

    public function isIncludeIds(): bool
    {
        return $this->ctxParams['includeIds'] ?? false;
    }
}
