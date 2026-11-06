<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Integration\Documents;

use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Pimcore\Model\Asset;
use Pimcore\Model\Document\Editable\Relation;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Version;
use Pimcore\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

class DocumentRelationImportExportTest extends KernelTestCase
{
    use ResetDatabase;

    private const ASSET_PATH = '/relation-asset.svg';
    private const PAGE_PATH = '/relation-page';

    private Exporter $exporter;

    /** @var Importer<\ArrayObject<int|string, mixed>, Page> */
    private Importer $importer;

    protected function setUp(): void
    {
        Version::disable();
        $this->exporter = self::getContainer()->get(Exporter::class);
        $this->importer = self::getContainer()->get(Importer::class);
    }

    public function test_export_without_ids_references_related_elements_by_path_and_key(): void
    {
        $asset = $this->createAsset();
        $this->createPageWithRelation($asset);

        $exported = $this->exportPage();

        self::assertArrayNotHasKey('id', $exported);
        self::assertSame(
            ['type' => 'asset', 'path' => '/', 'key' => 'relation-asset.svg'],
            $exported['editables']['relatedAsset']['data'],
        );
        self::assertSame(self::ASSET_PATH, $exported['properties']['relatedAsset']['value']);
    }

    public function test_export_with_ids_references_related_elements_by_id(): void
    {
        $asset = $this->createAsset();
        $this->createPageWithRelation($asset);

        $exported = $this->exportPage(true);

        self::assertSame(
            ['id' => $asset->getId(), 'type' => 'asset', 'subtype' => 'unknown'],
            $exported['editables']['relatedAsset']['data'],
        );
        self::assertSame($asset->getId(), $exported['properties']['relatedAsset']['value']);
    }

    public function test_import_without_ids_restores_relations_by_path_and_key(): void
    {
        $asset = $this->createAsset();
        $this->createPageWithRelation($asset);
        $yaml = $this->exporter->export([Page::getByPath(self::PAGE_PATH)], 'yaml');

        $oldAssetId = $asset->getId();
        Page::getByPath(self::PAGE_PATH)?->delete();
        $asset->delete();

        $newAsset = $this->createAsset();
        self::assertNotSame($oldAssetId, $newAsset->getId());

        $this->importer->import($yaml, 'yaml', true, true);

        $this->assertRelationsOfImportedPage($newAsset);
    }

    public function test_import_with_ids_restores_relations_by_id(): void
    {
        $asset = $this->createAsset();
        $this->createPageWithRelation($asset);
        $yaml = $this->exporter->export([Page::getByPath(self::PAGE_PATH)], 'yaml', ['includeIds' => true]);

        $this->importer->import($yaml, 'yaml', true, true);

        $this->assertRelationsOfImportedPage($asset);
    }

    private function assertRelationsOfImportedPage(Asset $expectedAsset): void
    {
        $importedPage = Page::getByPath(self::PAGE_PATH);
        self::assertInstanceOf(Page::class, $importedPage);

        $editable = $importedPage->getEditable('relatedAsset');
        self::assertInstanceOf(Relation::class, $editable);
        self::assertSame($expectedAsset->getId(), $editable->getId());

        $property = $importedPage->getProperty('relatedAsset', true);
        self::assertInstanceOf(\Pimcore\Model\Property::class, $property);
        self::assertInstanceOf(Asset::class, $property->getData());
        self::assertSame($expectedAsset->getId(), $property->getData()->getId());
    }

    private function createAsset(): Asset
    {
        $asset = new Asset();
        $asset->setParentId(1);
        $asset->setPath('/');
        $asset->setKey('relation-asset.svg');
        $asset->save();

        return $asset;
    }

    private function createPageWithRelation(Asset $asset): Page
    {
        $page = new Page();
        $page->setParentId(1);
        $page->setPath('/');
        $page->setKey('relation-page');
        $page->setTitle('Relation Page');
        $page->setController('/Some/Controller');
        $page->setProperty('relatedAsset', 'asset', $asset);
        $page->setRawEditable('relatedAsset', 'relation', [
            'id' => $asset->getId(),
            'type' => 'asset',
            'subtype' => 'unknown',
        ]);
        $page->save();

        return $page;
    }

    /**
     * @return array<string, mixed>
     */
    private function exportPage(bool $includeIds = false): array
    {
        $page = Page::getByPath(self::PAGE_PATH);
        self::assertInstanceOf(Page::class, $page);

        $data = Yaml::parse($this->exporter->export([$page], 'yaml', ['includeIds' => $includeIds]));

        return $data['elements'][0]['Pimcore\Model\Document\Page'];
    }
}
