<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Model\Element;
use Neusta\Pimcore\ImportExportBundle\Serializer\SerializerInterface;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Element\DuplicateFullPathException;

class Importer
{
    /**
     * @template TSource of Element
     * @template TTarget of AbstractElement
     *
     * @param array<class-string<TSource>, Converter<TSource, TTarget, GenericContext|null> $typeToConverterMap
     */
    public function __construct(
        private readonly array $typeToConverterMap,
        private readonly ParentRelationResolver $parentRelationResolver,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @return array<AbstractElement>
     *
     * @throws ConverterException
     * @throws DuplicateFullPathException
     */
    public function import(string $yamlInput, string $format, bool $forcedSave = true): array
    {
        $config = $this->serializer->deserialize($yamlInput, $format);

        if (!\is_array($config) || !\is_array($config[Element::ELEMENTS] ?? null)) {
            throw new \DomainException(\sprintf('Given data in format %s is not valid.', $format));
        }

        $elements = [];

        foreach ($config[Element::ELEMENTS] as $element) {
            $result = null;
            $typeKey = key($element);
            if (\array_key_exists($typeKey, $this->typeToConverterMap)) {
                $result = $this->typeToConverterMap[$typeKey]->convert(new \ArrayObject($element[$typeKey]));
                if ($forcedSave) {
                    $result = $this->parentRelationResolver->resolve($result);
                    if ($result) {
                        $result->save();
                    }
                }
            }
            if ($result) {
                $elements[] = $result;
            }
        }

        return $elements;
    }
    //
    // private function checkAndUpdatePage(AbstractElement $element): void
    // {
    //
    //    if (!$dao->getById($element->getParentId() ?? -1)) {
    //        $listing = $dao->getByPath($element->getPath());
    //        $existingParent = $listing->getByPath($element->getPath() ?? '');
    //        if (!$existingParent) {
    //            throw new \InvalidArgumentException('Neither parentId nor path leads to a valid parent element');
    //        }
    //        $element->setParentId($existingParent->getId());
    //    }
    // }
}
