<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Populator;

use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Populator;
use Neusta\Pimcore\HeadlessBundle\Converter\Context\Locale;
use Neusta\Pimcore\ImportExportBundle\Converter\FieldConverter;
use Neusta\Pimcore\ImportExportBundle\Model\Object\Data\NullProperty;
use Neusta\Pimcore\ImportExportBundle\Model\Object\DataObject;
use Neusta\Pimcore\ImportExportBundle\PimcoreConverter\Context\Fieldname;
use Neusta\Pimcore\Toolbox\Wrapper\Tool;
use Pimcore\Model\DataObject as PimcoreDataObject;
use Pimcore\Model\DataObject\ClassDefinition\Data\Localizedfields;

/**
 * @implements Populator<PimcoreDataObject, DataObject, GenericContext|null>
 */
class DataObjectExportFieldsPopulator implements Populator
{
    public function __construct(
        private FieldConverter $fieldConverter,
        private Tool $pimcoreTool,
    ) {
    }

    /**
     * @param PimcoreDataObject   $source
     * @param DataObject          $target
     * @param GenericContext|null $ctx
     */
    public function populate(object $target, object $source, ?object $ctx = null): void
    {
        if (!$source instanceof PimcoreDataObject\Concrete || null === $ctx) {
            return;
        }

        foreach ($source->getClass()->getFieldDefinitions() as $fieldName => $definition) {
            if ($definition instanceof Localizedfields) {
                $this->handleLocalizedFields($target, $definition, $source, $ctx);
            } else {
                $this->handleFields($source, $ctx, $fieldName, $target);
            }
        }
    }

    private function handleLocalizedFields(DataObject $target, Localizedfields $definition, PimcoreDataObject $source, GenericContext $ctx): void
    {
        foreach ($this->pimcoreTool->getValidLanguages() as $language) {
            $target->localizedFields[$language] = [];
            foreach ($definition->getChildren() as $childFieldDefinition) {
                try {
                    $value = $this->fieldConverter->convert(
                        $source,
                        $ctx
                            ->with(new Locale($language))
                            ->with(new Fieldname($childFieldDefinition->getName())
                            )
                    );
                } catch (\InvalidArgumentException $e) {
                    $value = null;
                }

                $target->localizedFields[$language][$childFieldDefinition->getName()]
                    = $value;
            }
        }
    }

    /**
     * @param object|PimcoreDataObject|PimcoreDataObject\Concrete $source
     * @param object|GenericContext|null                          $ctx
     * @param int|string                                          $fieldName
     * @param object|DataObject                                   $target
     *
     * @throws \Neusta\ConverterBundle\Exception\ConverterException
     */
    public function handleFields(PimcoreDataObject $source, GenericContext $ctx, string $fieldName, DataObject $target): void
    {
        try {
            $value = $this->fieldConverter->convert($source, $ctx->with(new Fieldname($fieldName)));
        } catch (\InvalidArgumentException $e) {
            // if no converter has been found, the field type is currently not supported
            return;
        }

        if (!$value instanceof NullProperty) {
            $target->fields[$fieldName] = $value;
        }
    }
}
