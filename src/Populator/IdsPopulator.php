<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\ImportExportBundle\Model\Element;
use Pimcore\Model\Element\AbstractElement;

/**
 * @implements Populator<AbstractElement, Element ,\Neusta\ConverterBundle\Converter\Context\GenericContext|null>
 */
class IdsPopulator implements Populator
{
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if ($ctx?->hasKey('includeIds') && true === $ctx->getValue('includeIds')) {
            $target->id = $source->getId();
            $target->parentId = $source->getParentId();
        }
    }
}
