<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Converter;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\HeadlessBundle\Converter\Context\Locale;
use Neusta\Pimcore\ImportExportBundle\Model\Object\Data\NullProperty;
use Neusta\Pimcore\ImportExportBundle\Model\Object\Data\StringProperty;
use Neusta\Pimcore\ImportExportBundle\PimcoreConverter\Context\Fieldname;
use Pimcore\Model\DataObject as PimcoreDataObject;

/**
 * @implements Converter<PimcoreDataObject, GenericContext|null>
 */
class FieldConverter implements Converter
{
    public function __construct(
        private TypeStrategyConverter $typeStrategyConverter,
    ) {
    }

    public function convert(object $source, ?object $ctx = null): object
    {
        $fieldname = $ctx->get(Fieldname::class);
        if (!$fieldname) {
            throw new ConverterException('Fieldname has not been set - FieldConverter can not convert unknown field.');
        }
        $locale = $ctx?->get(Locale::class) ?? null;

        $pimcoreFieldValue = $source->{'get' . ucfirst($fieldname)}($locale);
        if (!$pimcoreFieldValue) {
            return new NullProperty();
        }
        if (\is_string($pimcoreFieldValue)) {
            $stringProperty = new StringProperty();
            $stringProperty->key = $fieldname;
            $stringProperty->type = 'string';
            $stringProperty->value = $pimcoreFieldValue;

            return $stringProperty;
        }

        return $this->typeStrategyConverter->convert($pimcoreFieldValue, $ctx);
    }
}
