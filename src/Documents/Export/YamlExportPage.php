<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Export;

class YamlExportPage
{
    public int $id;
    public int $parentId;
    public string $type = 'page';
    public bool $published = false;
    public string $path;
    public string $language;
    public string $navigation_name;
    public string $navigation_title;
    public string $key;
    public string $title;
    public string $controller;
    /** @var array<YamlExportEditable> */
    public array $editables;

    /**
     * @param array<string, mixed>|null $yamlConfig
     */
    public function __construct(
        ?array $yamlConfig = null,
    ) {
        if ($yamlConfig) {
            foreach ($yamlConfig as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }
    }
}
