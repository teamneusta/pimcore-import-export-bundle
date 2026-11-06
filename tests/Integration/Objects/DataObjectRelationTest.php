<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Integration\Objects;

use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Pimcore\Db;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data\Input;
use Pimcore\Model\DataObject\ClassDefinition\Data\ManyToManyRelation;
use Pimcore\Model\DataObject\ClassDefinition\Data\ManyToOneRelation;
use Pimcore\Model\DataObject\ClassDefinition\Layout\Panel;
use Pimcore\Model\DataObject\PiebRelationTest;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Version;
use Pimcore\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @see PiebRelationTest
 */
class DataObjectRelationTest extends KernelTestCase
{
    use ResetDatabase;

    private const CLASS_ID = 'PIEB_RELATION_TEST';
    private const CLASS_NAME = 'PiebRelationTest';

    private Exporter $exporter;

    /** @var Importer<\ArrayObject<int|string, mixed>, PiebRelationTest> */
    private Importer $importer;

    protected function setUp(): void
    {
        Version::disable();
        $this->exporter = self::getContainer()->get(Exporter::class);
        $this->importer = self::getContainer()->get(Importer::class);

        $this->createRelationClass();
    }

    public function test_export_relations_without_ids_contains_path_and_key(): void
    {
        $object = $this->createObjectWithRelations();

        $exported = $this->parseObject($this->exportObjectYaml($object));

        $relations = $exported['relations'];

        self::assertSame(
            [
                'Pimcore\Model\Asset' => [
                    'type' => 'unknown',
                    'path' => '/',
                    'key' => 'related-asset.svg',
                    'filename' => '',
                ],
            ],
            $relations['relatedAsset'],
        );
        self::assertArrayNotHasKey('id', $exported);
        self::assertArrayNotHasKey('id', $relations['relatedAsset']['Pimcore\Model\Asset']);
        self::assertArrayNotHasKey('parentId', $relations['relatedAsset']['Pimcore\Model\Asset']);

        self::assertCount(2, $relations['relatedDocuments']);
        foreach ($relations['relatedDocuments'] as $relation) {
            self::assertArrayHasKey('Pimcore\Model\Document', $relation);
            self::assertSame('/', $relation['Pimcore\Model\Document']['path']);
            self::assertStringStartsWith('related-document-', $relation['Pimcore\Model\Document']['key']);
            self::assertArrayNotHasKey('id', $relation['Pimcore\Model\Document']);
        }
    }

    public function test_export_relations_with_ids_contains_ids(): void
    {
        $object = $this->createObjectWithRelations();
        $asset = Asset::getByPath('/related-asset.svg');
        self::assertInstanceOf(Asset::class, $asset);

        $exported = $this->parseObject($this->exportObjectYaml($object, true));

        $relationAsset = $exported['relations']['relatedAsset']['Pimcore\Model\Asset'];

        self::assertSame($asset->getId(), $relationAsset['id']);
        self::assertSame($asset->getParentId(), $relationAsset['parentId']);
        self::assertSame('/', $relationAsset['path']);
        self::assertSame('related-asset.svg', $relationAsset['key']);

        foreach ($exported['relations']['relatedDocuments'] as $relation) {
            self::assertArrayHasKey('id', $relation['Pimcore\Model\Document']);
        }
    }

    public function test_import_relations_without_ids_resolves_by_path_and_key(): void
    {
        $object = $this->createObjectWithRelations();
        $yaml = $this->exportObjectYaml($object);

        $oldAssetId = Asset::getByPath('/related-asset.svg')?->getId();
        $this->deleteRelatedElements();

        // re-create the related elements with the same path and key but new IDs
        $newAsset = $this->createAsset();
        self::assertNotSame($oldAssetId, $newAsset->getId());
        $this->createDocument(1);
        $this->createDocument(2);

        $imported = $this->importObject($yaml);

        self::assertNotNull($imported->getRelatedAsset());
        self::assertSame($newAsset->getId(), $imported->getRelatedAsset()->getId());
        self::assertSame(
            ['/related-document-1', '/related-document-2'],
            array_map(
                static fn (Page $page) => $page->getPath() . $page->getKey(),
                $imported->getRelatedDocuments(),
            ),
        );
    }

    public function test_import_relations_with_ids_resolves_by_id(): void
    {
        $object = $this->createObjectWithRelations();
        $yaml = $this->exportObjectYaml($object, true);

        $asset = Asset::getByPath('/related-asset.svg');
        self::assertInstanceOf(Asset::class, $asset);

        $imported = $this->importObject($yaml);

        self::assertSame($asset->getId(), $imported->getRelatedAsset()->getId());
        self::assertSame(
            ['/related-document-1', '/related-document-2'],
            array_map(
                static fn (Page $page) => $page->getPath() . $page->getKey(),
                $imported->getRelatedDocuments(),
            ),
        );
    }

    private function exportObjectYaml(PiebRelationTest $object, bool $includeIds = false): string
    {
        return $this->exporter->export([$object], 'yaml', ['includeIds' => $includeIds]);
    }

    /**
     * @return array<string, mixed>
     */
    private function parseObject(string $yaml): array
    {
        $data = Yaml::parse($yaml);

        return $data['elements'][0]['Pimcore\Model\DataObject'];
    }

    private function importObject(string $yaml): PiebRelationTest
    {
        $importedElements = $this->importer->import($yaml, 'yaml', false, false);

        self::assertCount(1, $importedElements);
        self::assertInstanceOf(PiebRelationTest::class, $importedElements[0]);

        return $importedElements[0];
    }

    private function createObjectWithRelations(): PiebRelationTest
    {
        $asset = $this->createAsset();
        $documents = [$this->createDocument(1), $this->createDocument(2)];

        $object = new PiebRelationTest();
        $object->setParentId(1);
        $object->setPath('/');
        $object->setKey('relation-object');
        $object->setText('Hello relations');
        $object->setRelatedAsset($asset);
        $object->setRelatedDocuments($documents);
        $object->save();

        return $object;
    }

    private function createAsset(): Asset
    {
        $asset = new Asset();
        $asset->setParentId(1);
        $asset->setPath('/');
        $asset->setKey('related-asset.svg');
        $asset->save();

        return $asset;
    }

    private function createDocument(int $index): Page
    {
        $document = new Page();
        $document->setParentId(1);
        $document->setPath('/');
        $document->setKey('related-document-' . $index);
        $document->setTitle('Related Document ' . $index);
        $document->save();

        return $document;
    }

    private function deleteRelatedElements(): void
    {
        foreach (['/related-asset.svg', '/related-document-1', '/related-document-2'] as $path) {
            $element = Asset::getByPath($path) ?? Page::getByPath($path);
            $element?->delete();
        }
    }

    private function createRelationClass(): void
    {
        $text = new Input();
        $text->setName('text');

        $relatedAsset = new ManyToOneRelation();
        $relatedAsset->setName('relatedAsset');
        $relatedAsset->setAssetsAllowed(true);

        $relatedDocuments = new ManyToManyRelation();
        $relatedDocuments->setName('relatedDocuments');
        $relatedDocuments->setDocumentsAllowed(true);

        $panel = (new Panel())->setName('MyLayout');
        $panel->addChild($text);
        $panel->addChild($relatedAsset);
        $panel->addChild($relatedDocuments);

        $root = new Panel('root');
        $root->addChild($panel);

        // The database is reset between tests, but the class specific tables are not.
        // Dropping them keeps the object IDs deterministic.
        foreach (['object_', 'object_query_', 'object_relations_', 'object_store_'] as $prefix) {
            Db::get()->executeStatement('DROP TABLE IF EXISTS `' . $prefix . self::CLASS_ID . '`');
        }

        $class = new ClassDefinition();
        $class->setName(self::CLASS_NAME);
        $class->setId(self::CLASS_ID);
        $class->setLayoutDefinitions($root);
        $class->save();
        $class->generateClassFiles(false);

        require_once PIMCORE_CLASS_DIRECTORY . '/DataObject/' . self::CLASS_NAME . '.php';
    }
}
