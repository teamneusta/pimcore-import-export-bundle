<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Import;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Documents\Export\YamlExportPage;
use Pimcore\Model\Document;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Element\DuplicateFullPathException;
use Symfony\Component\Yaml\Yaml;

class PageImporter
{
    /**
     * @param Converter<YamlExportPage, Page, GenericContext|null> $yamlToPageConverter
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
    public function parseYaml(string $yamlInput, bool $forcedSave = true): array
    {
        $config = Yaml::parse($yamlInput);

        if (!\is_array($config) || !\is_array($config[YamlExportPage::PAGES] ?? null)) {
            throw new \DomainException('Given YAML is not valid.');
        }

        $pages = [];

        foreach ($config[YamlExportPage::PAGES] as $configPage) {
            $page = null;
            if (\is_array($configPage[YamlExportPage::PAGE])) {
                $page = $this->yamlToPageConverter->convert(new YamlExportPage($configPage[YamlExportPage::PAGE]));
                if ($forcedSave) {
                    $this->checkAndUpdatePage($page, $config[YamlExportPage::PAGES]);
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
     * @param array<string, mixed> $params
     */
    public function readYamlFileAndSetParameters(string $filename, array $params = []): string
    {
        if (($yamlFile = file_get_contents($filename)) !== false) {
            foreach ($params as $key => $paramValue) {
                $yamlFile = str_replace($key, (string) $paramValue, $yamlFile);
            }

            return $yamlFile;
        }

        return '';
    }

    /**
     * @param array<string, array<string, mixed>> $configPages
     */
    private function checkAndUpdatePage(Page $page, array &$configPages): void
    {
        $oldPath = $page->getPath();
        $existingParent = Document::getById($page->getParentId() ?? -1);
        if (!$existingParent) {
            $existingParent = Document::getByPath($page->getPath() ?? '');
            if (!$existingParent) {
                throw new \InvalidArgumentException('Neither parentId nor path leads to a valid parent element');
            }
            $page->setParentId($existingParent->getId());
            $newPath = $existingParent->getPath() . $page->getKey() . '/';
            foreach ($configPages as $configPage) {
                if (\array_key_exists('path', $configPage[YamlExportPage::PAGE]) && \is_string($oldPath)) {
                    str_replace($oldPath, $newPath, $configPage[YamlExportPage::PAGE]['path']);
                }
            }
        }
    }
}
