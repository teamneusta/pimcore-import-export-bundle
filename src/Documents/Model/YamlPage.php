<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Model;

class YamlPage
{
    public const PAGES = 'pages';
    public const PAGE = 'page';

    public ?int $id = null;
    public int $parentId = 0;
    public string $type = 'page';
    public bool $published = false;
    public string $path = '';
    public string $language = '';
    public ?string $navigation_name = null;
    public ?string $navigation_title = null;
    public string $key = '';
    public ?string $title = null;
    public ?string $controller = null;
    /** @var array<YamlEditable> */
    public array $editables = [];

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
