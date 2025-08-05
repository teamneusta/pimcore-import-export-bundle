<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Model\Document;

use Neusta\Pimcore\ImportExportBundle\Model\Element;
use Neusta\Pimcore\ImportExportBundle\Model\Property;

class Document extends Element
{
    public bool $published = false;

    /** @var array<Property> - property key will be mapped to */
    public array $properties = [];

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
