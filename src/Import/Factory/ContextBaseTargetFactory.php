<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\Factory;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\ConverterBundle\TargetFactory;
use Pimcore\Model\DataObject;

/**
 * @implements TargetFactory<DataObject, GenericContext|null>
 */
class ContextBaseTargetFactory implements TargetFactory
{
    public const TARGET_TYPE = 'type';

    public function create(?object $ctx = null): object
    {
        if (!$ctx) {
            throw new ConverterException('Target object could not be created because of missing Context');
        }

        $type = $ctx->getValue(self::TARGET_TYPE);
        if (!$type instanceof DataObject) {
            throw new ConverterException($type . ' should be subtype of DataObject');
        }

        return new ($type)();
    }
}
