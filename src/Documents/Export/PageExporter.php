<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Export;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Pimcore\Model\Document\Page;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Yaml;

class PageExporter
{
    private const PAGE = 'page';
    private const YAML_DUMP_FLAGS =
        Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE |
        Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK |
        Yaml::DUMP_NULL_AS_TILDE;

    /**
     * @param Converter<Page, YamlExportPage, GenericContext|null> $pageToYamlConverter
     */
    public function __construct(
        private readonly Converter $pageToYamlConverter,
        private readonly SerializerInterface $serializer,
    ) {
    }

    public function toYaml(Page $page): string
    {
        $yamlExportPage = $this->pageToYamlConverter->convert($page);

        return $this->serializer->serialize(
            [self::PAGE => $yamlExportPage],
            'yaml',
            ['yaml_inline' => 4, 'yaml_indent' => 4, 'yaml_flags' => self::YAML_DUMP_FLAGS],
        );
    }
}
