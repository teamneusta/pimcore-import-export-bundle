<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Property;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Property as PimcoreProperty;

/**
 * @implements Populator<PimcoreProperty, Property ,\Neusta\ConverterBundle\Converter\Context\GenericContext|null>
 */
class PropertyValuePopulator implements Populator
{
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if ($source->getData() instanceof AbstractElement) {
            $target->value = $source->getData()->getPath() . $source->getData()->getKey();
        } else {
            $target->value = $source->getData();
        }
    }
}
