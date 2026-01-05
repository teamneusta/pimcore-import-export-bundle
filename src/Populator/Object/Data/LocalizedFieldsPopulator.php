<?php declare(strict_types=1);

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Object\DataObject;
use Pimcore\Model\DataObject as PimcoreDataObject;

/**
 * @implements Populator<PimcoreDataObject, DataObject, GenericContext|null>
 */
class LocalizedFieldsPopulator implements Populator
{
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        // TODO: Implement populate() method.
    }
}
