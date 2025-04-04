<?php

namespace Neusta\Pimcore\ImportExportBundle\Import\Factory;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\ConverterBundle\TargetFactory;

/**
 * @implements TargetFactory<GenericContext|null>
 */
class ContextBaseTargetFactory implements TargetFactory
{
    public const TARGET_TYPE = 'type';
    /**
     * @inheritDoc
     */
    public function create(?object $ctx = null): object
    {
        if (!$ctx) {
            throw new ConverterException('Target object could not be created because of missing Context');
        }
        return new ($ctx->getValue(self::TARGET_TYPE))();
    }
}
