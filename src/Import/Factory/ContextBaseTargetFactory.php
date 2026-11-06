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
        if (!\is_string($type) || !class_exists($type) || !is_a($type, DataObject::class, true)) {
            throw new ConverterException(\sprintf('%s should be subtype of %s', \is_string($type) ? $type : get_debug_type($type), DataObject::class));
        }

        return new $type();
    }
}
