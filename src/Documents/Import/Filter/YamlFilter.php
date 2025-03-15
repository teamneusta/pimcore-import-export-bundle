<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Import\Filter;

interface YamlFilter
{
    public function filterAndReplace(string $yamlContent): string;
}
