<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Import;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Documents\Model\YamlPage;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Element\DuplicateFullPathException;
use Symfony\Component\Yaml\Yaml;

class PageImporter
{
    /**
     * @param Converter<YamlPage, Page, GenericContext|null> $yamlToPageConverter
     */
    public function __construct(
        private readonly Converter $yamlToPageConverter,
    ) {
    }

    /**
     * @return array<Page>
     *
     * @throws ConverterException
     * @throws DuplicateFullPathException
     */
    public function fromYaml(string $yamlInput, bool $forcedSave = true): array
    {
        $config = Yaml::parse($yamlInput);

        if (!\is_array($config) || !\is_array($config[YamlPage::PAGES] ?? null)) {
            throw new \DomainException('Given YAML is not valid.');
        }

        $pages = [];

        foreach ($config[YamlPage::PAGES] as $configPage) {
            $page = null;
            if (\is_array($configPage[YamlPage::PAGE])) {
                $page = $this->yamlToPageConverter->convert(new YamlPage($configPage[YamlPage::PAGE]));
                if ($forcedSave) {
                    $this->checkAndUpdatePage($page, $config[YamlPage::PAGES]);
                    $page->save();
                }
            }
            if ($page) {
                $pages[] = $page;
            }
        }

        return $pages;
    }

    /**
     * @param array<string, array<string, mixed>> $configPages
     */
    private function checkAndUpdatePage(Page $page, array &$configPages): void
    {
        $oldPath = $page->getPath();

        if (!Document::getById($page->getParentId() ?? -1)) {
            $existingParent = Document::getByPath($page->getPath() ?? '');
            if (!$existingParent) {
                throw new \InvalidArgumentException('Neither parentId nor path leads to a valid parent element');
            }
            $page->setParentId($existingParent->getId());
            $newPath = $existingParent->getPath() . $page->getKey() . '/';
            foreach ($configPages as $configPage) {
                if (\array_key_exists('path', $configPage[YamlPage::PAGE]) && \is_string($oldPath)) {
                    str_replace($oldPath, $newPath, $configPage[YamlPage::PAGE]['path']);
                }
            }
        }
    }
}
