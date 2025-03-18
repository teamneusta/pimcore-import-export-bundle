<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Export;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Documents\Model\Page;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Folder;
use Pimcore\Model\Document\Page as PimcorePage;
use Pimcore\Model\Document\PageSnippet;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Yaml\Yaml;

class PageExporter
{
    public const YAML_DUMP_FLAGS =
        Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE |
        Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK |
        Yaml::DUMP_NULL_AS_TILDE;

    /**
     * @param Converter<Document, Page, GenericContext|null> $pageToYamlConverter
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
     * @param iterable<Document> $pages
     *
     * @throws ConverterException
     */
    public function toYaml(iterable $pages): string
    {
        $yamlExportPages = [];
        foreach ($pages as $page) {
            if (
                $page instanceof PimcorePage
                || $page instanceof PageSnippet
                || $page instanceof Folder
            ) {
                $yamlExportPages[] = [Page::PAGE => $this->pageToYamlConverter->convert($page)];
            }
        }

        return $this->serializer->serialize(
            [Page::PAGES => $yamlExportPages],
            'yaml',
            [
                'yaml_inline' => 6,
                'yaml_indent' => 0,
                'yaml_flags' => self::YAML_DUMP_FLAGS,
            ]
        );
    }
}
