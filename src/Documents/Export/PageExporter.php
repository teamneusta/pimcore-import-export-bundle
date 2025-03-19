<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Export;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Documents\Model\Page;
use Neusta\Pimcore\ImportExportBundle\Serializer\SerializerInterface;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Folder;
use Pimcore\Model\Document\Page as PimcorePage;
use Pimcore\Model\Document\PageSnippet;

class PageExporter
{
    /**
     * @param Converter<Document, Page, GenericContext|null> $pageToYamlConverter
     */
    public function __construct(
        private readonly Converter $pageToYamlConverter,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * Exports one or more pages in the given format (yaml, json, ...)).
     *
     * @param iterable<Document> $pages
     *
     * @throws ConverterException
     */
    public function export(iterable $pages, string $format): string
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

        return $this->serializer->serialize([Page::PAGES => $yamlExportPages], $format);
    }
}
