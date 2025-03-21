<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Model\Document;

use Neusta\Pimcore\ImportExportBundle\Model\Element;

class Page extends Element
{
    public bool $published = false;
    public ?string $navigation_name = null;
    public ?string $navigation_title = null;
    public ?string $title = null;
    public ?string $controller = null;
    /** @var array<Editable> */
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
