<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Export;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Pimcore\Model\Document\Page;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Yaml;

class PageExporter
{
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

    /**
     * Exports one or more pages as YAML with the following structure:
     * pages:
     *   - page:
     *      key: 'page_key_1'
     *   - page:
     *     key: 'page_key_2'
     * ...
     *
     * @param Page|iterable<Page> $pages
     *
     * @throws ConverterException
     *
     * @deprecated parameter type Page will not be allowed in further versions
     */
    public function toYaml(Page|iterable $pages): string
    {
        // @deprecated - should be removed after changing the method signature
        if ($pages instanceof Page) {
            $pages = [$pages];
        }

        $yamlExportPages = [];
        foreach ($pages as $page) {
            $yamlExportPages[] = [YamlExportPage::PAGE => $this->pageToYamlConverter->convert($page)];
        }

        return $this->serializer->serialize(
            [YamlExportPage::PAGES => $yamlExportPages],
            'yaml',
            [
                'yaml_inline' => 4,
                'yaml_indent' => 0,
                'yaml_flags' => self::YAML_DUMP_FLAGS,
            ]
        );
    }
}
