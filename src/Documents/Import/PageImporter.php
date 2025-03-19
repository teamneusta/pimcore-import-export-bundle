<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Import;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Documents\Model\Page;
use Neusta\Pimcore\ImportExportBundle\Serializer\SerializerInterface;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Page as PimcorePage;
use Pimcore\Model\Element\DuplicateFullPathException;

class PageImporter
{
    /**
     * @param Converter<Page, PimcorePage, GenericContext|null> $yamlToPageConverter
     */
    public function __construct(
        private readonly Converter $yamlToPageConverter,
        private readonly SerializerInterface $serializer,
    ) {
    }

    /**
     * @return array<PimcorePage>
     *
     * @throws ConverterException
     * @throws DuplicateFullPathException
     */
    public function import(string $yamlInput, string $format, bool $forcedSave = true): array
    {
        $config = $this->serializer->deserialize($yamlInput, $format);

        if (!\is_array($config) || !\is_array($config[Page::PAGES] ?? null)) {
            throw new \DomainException(\sprintf('Given data in format %s is not valid.', $format));
        }

        $pages = [];

        foreach ($config[Page::PAGES] as $configPage) {
            $page = null;
            if (\is_array($configPage[Page::PAGE])) {
                $page = $this->yamlToPageConverter->convert(new Page($configPage[Page::PAGE]));
                if ($forcedSave) {
                    $this->checkAndUpdatePage($page);
                    $page->save();
                }
            }
            if ($page) {
                $pages[] = $page;
            }
        }

        return $pages;
    }

    private function checkAndUpdatePage(Document $page): void
    {
        if (!Document::getById($page->getParentId() ?? -1)) {
            $existingParent = Document::getByPath($page->getPath() ?? '');
            if (!$existingParent) {
                throw new \InvalidArgumentException('Neither parentId nor path leads to a valid parent element');
            }
            $page->setParentId($existingParent->getId());
        }
    }
}
