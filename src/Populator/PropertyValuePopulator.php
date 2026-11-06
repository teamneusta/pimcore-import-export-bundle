<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Property;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Property as PimcoreProperty;

/**
 * Exports the value of a document property.
 *
 * A property which references another element is exported by its ID if the context
 * contains "includeIds" => true and by its path and key otherwise.
 *
 * @implements Populator<PimcoreProperty, Property ,\Neusta\ConverterBundle\Converter\Context\GenericContext|null>
 */
class PropertyValuePopulator implements Populator
{
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        $data = $source->getData();

        if (!$data instanceof AbstractElement) {
            $target->value = $data;

            return;
        }

        if ($ctx?->hasKey('includeIds') && true === $ctx->getValue('includeIds')) {
            $target->value = $data->getId();

            return;
        }

        $target->value = $data->getPath() . $data->getKey();
    }
}
