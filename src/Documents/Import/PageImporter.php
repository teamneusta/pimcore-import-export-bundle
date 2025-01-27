<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Import;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Converter\Context\GenericContext;
use Neusta\Pimcore\ImportExportBundle\Documents\Export\YamlExportPage;
use Pimcore\Model\Document\Page;
use Symfony\Component\Yaml\Yaml;

class PageImporter
{
    private const PAGE = 'page';

    /**
     * @param Converter<YamlExportPage, Page, GenericContext|null> $yamlToPageConverter
     */
    public function __construct(
        private readonly Converter $yamlToPageConverter,
    ) {
    }

    public function parseYaml(string $yamlInput, bool $forcedSave = true): mixed
    {
        $config = Yaml::parse($yamlInput);

        if (!\is_array($config) || !\is_array($config[self::PAGE] ?? null)) {
            throw new \DomainException('Given YAML is not a valid page.');
        }

        $page = $this->yamlToPageConverter->convert(new YamlExportPage($config[self::PAGE]));
        if ($forcedSave) {
            $page->save();
        }

        return $page;
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
}
