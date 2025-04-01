<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Document\Editable;
use Pimcore\Model\Document\Editable as PimcoreEditable;

/**
 * @implements Populator<PimcoreEditable, Editable, GenericContext|null>
 */
class EditableDataPopulator implements Populator
{
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if ($source instanceof PimcoreEditable\Relation || $source instanceof PimcoreEditable\Relations) {
            $target->data = $source->getDataForResource();
        } else {
            $target->data = $source->getData();
        }
    }
}
