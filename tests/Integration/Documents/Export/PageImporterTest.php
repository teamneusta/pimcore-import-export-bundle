<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Integration\Documents\Export;

use Neusta\Pimcore\ImportExportBundle\Documents\Import\PageImporter;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Pimcore\Test\KernelTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class PageImporterTest extends KernelTestCase
{
    use MatchesSnapshots;
    use ResetDatabase;

    private PageImporter $importer;

    protected function setUp(): void
    {
        $this->importer = self::getContainer()->get(PageImporter::class);
    }

    public function testSinglePageImport_exceptional_case(): void
    {
        $yaml =
            <<<YAML
            pages:
                -
                    page:
                        id: 999
                        path: /path-does-not-exist/
                        key: test_document_1
            YAML;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Neither parentId nor path leads to a valid parent element');
        $this->importer->parseYaml($yaml);
    }

    public function testSinglePageExport_regular_case_parent_id(): void
    {
        $yaml =
            <<<YAML
            pages:
                -
                    page:
                        id: 999
                        parentId: 1
                        type: email
                        published: false
                        path: /path/will/be/overwritten/by/parent_id/
                        language: fr
                        navigation_name: 'Mein Dokument'
                        navigation_title: 'Mein Dokument - Titel'
                        key: test_document_1
                        title: 'Titel meines Dokuments'
                        controller: Irgend/ein/Controller
            YAML;

        $pages = $this->importer->parseYaml($yaml);
        self::assertEquals(999, $pages[0]->getId());
        self::assertEquals('/', $pages[0]->getPath());

        self::assertEquals('test_document_1', $pages[0]->getKey());
        self::assertEquals('The Titel of My Document', $pages[0]->getTitle());
        self::assertEquals('email', $pages[0]->getType());
        self::assertEquals('/Some/Controller', $pages[0]->getController());
        self::assertEquals('en', $pages[0]->getProperty('language'));
        self::assertEquals('My Document', $pages[0]->getProperty('navigation_name'));
        self::assertEquals('My Document - Title', $pages[0]->getProperty('navigation_title'));
    }

    public function testSinglePageExport_regular_case_path(): void
    {
        $yaml =
            <<<YAML
            pages:
                -
                    page:
                        id: 999
                        parentId: 99999
                        type: email
                        published: false
                        path: /
                        language: en
                        navigation_name: 'My Document'
                        navigation_title: 'My Document - Title'
                        key: test_document_1
                        title: 'The Title of My Document'
                        controller: /Some/Controller
            YAML;

        $pages = $this->importer->parseYaml($yaml);
        self::assertEquals(999, $pages[0]->getId());
        self::assertEquals('/', $pages[0]->getPath());
        self::assertEquals(1, $pages[0]->getParentId());

        self::assertEquals('test_document_1', $pages[0]->getKey());
        self::assertEquals('The Title of My Document', $pages[0]->getTitle());
        self::assertEquals('email', $pages[0]->getType());
        self::assertEquals('/Some/Controller', $pages[0]->getController());
        self::assertEquals('en', $pages[0]->getProperty('language'));
        self::assertEquals('My Document', $pages[0]->getProperty('navigation_name'));
        self::assertEquals('My Document - Title', $pages[0]->getProperty('navigation_title'));
    }

    public function testSinglePageImport_tree_case(): void
    {
        $yaml =
            <<<YAML
            pages:
                -
                    page:
                        parentId: 1
                        id: 999
                        path: /my_path/
                        key: test_document_1
                -
                    page:
                        parentId: 999
                        id: 1000
                        path: /my_path/test_document_1/
                        key: test_document_1_1
                -
                    page:
                        parentId: 1000
                        id: 1001
                        path: /my_path/test_document_1/test_document_1_1/
                        key: test_document_1_1_1
            YAML;

        $pages = $this->importer->parseYaml($yaml);

        self::assertEquals('/test_document_1/test_document_1_1/', $pages[2]->getPath());
    }
}
