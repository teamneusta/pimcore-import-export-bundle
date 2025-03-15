<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Import\Filter;

class SearchAndReplaceFilter implements YamlFilter
{
    /**
     * @param array<string, mixed> $params
     */
    public function __construct(
        private array $params = [],
    ) {
    }

    public function filterAndReplace(string $yamlContent): string
    {
        foreach ($this->params as $key => $paramValue) {
            $yamlContent = str_replace($key, (string) $paramValue, $yamlContent);
        }

        return $yamlContent;
    }
}
