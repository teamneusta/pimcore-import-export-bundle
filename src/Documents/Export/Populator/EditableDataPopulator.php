<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Export\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Documents\Model\YamlEditable;
use Pimcore\Model\Document\Editable;

/**
 * @implements Populator<Editable, YamlEditable, GenericContext|null>
 */
class EditableDataPopulator implements Populator
{
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if ($source instanceof Editable\Relation || $source instanceof Editable\Relations) {
            $target->data = $source->getDataForResource();
        } else {
            $target->data = $source->getData();
        }
    }
}
