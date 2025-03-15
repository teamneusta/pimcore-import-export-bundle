<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Integration\Documents\Export;

use Neusta\Pimcore\ImportExportBundle\Documents\Export\PageExporter;
use Neusta\Pimcore\ImportExportBundle\Tests\Integration\Documents\ImportExportYamlDriver;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Pimcore\Model\Document\Editable\Input;
use Pimcore\Model\Document\Page;
use Pimcore\Test\KernelTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class PageExporterTest extends KernelTestCase
{
    use MatchesSnapshots;
    use ResetDatabase;

    private PageExporter $exporter;

    protected function setUp(): void
    {
        $this->exporter = self::getContainer()->get(PageExporter::class);
    }

    public function testSinglePageExport(): void
    {
        $page = new Page();
        $page->setId(999);
        $page->setParentId(4);
        $page->setType('email');
        $page->setPublished(false);
        $page->setPath('/test/');
        $page->setKey('test_document_1');
        $page->setProperty('language', 'string', 'en');
        $page->setProperty('navigation_name', 'string', 'My Document');
        $page->setProperty('navigation_title', 'string', 'My Document - Title');
        $page->setTitle('The Title of my document');
        $page->setController('/Some/Controller');
        $inputEditable = new Input();
        $inputEditable->setName('textInput');
        $inputEditable->setDataFromResource('some text input');
        $page->setEditables([$inputEditable]);

        $yaml = $this->exporter->exportToYaml([$page]);
        $this->assertMatchesSnapshot($yaml, new ImportExportYamlDriver());
    }

    public function testSimpleSavedPagesExport(): void
    {
        $page1 = new Page();
        $page1->setParentId(1);
        $page1->setKey('test_document_1');
        $page1->setTitle('Test Document_1');
        $page1->save();

        $page2 = new Page();
        $page2->setParentId(1);
        $page2->setKey('test_document_2');
        $page2->setTitle('Test Document_2');
        $page2->save();

        $yaml = $this->exporter->exportToYaml([$page1, $page2]);
        $this->assertMatchesSnapshot($yaml, new ImportExportYamlDriver());
    }

    public function testSimpleUnsavedPagesExport(): void
    {
        $page1 = new Page();
        $page1->setParentId(1);
        $page1->setKey('test_document_1');
        $page1->setPath('/');

        $page2 = new Page();
        $page2->setParentId(1);
        $page2->setKey('test_document_2');
        $page2->setPath('/');

        $yaml = $this->exporter->exportToYaml([$page1, $page2]);
        $this->assertMatchesSnapshot($yaml, new ImportExportYamlDriver());
    }

    public function testTreePagesExport(): void
    {
        $page1 = new Page();
        $page1->setParentId(1);
        $page1->setKey('test_document_1');
        $page1->setTitle('Test Document_1');
        $page1->save();

        $page2 = new Page();
        $page2->setParentId($page1->getId());
        $page2->setKey('test_document_2');
        $page2->setTitle('Test Document_2');
        $page2->save();

        $page3 = new Page();
        $page3->setParentId($page2->getId());
        $page3->setKey('test_document_2');
        $page3->setTitle('Test Document_2');
        $page3->save();

        $yaml = $this->exporter->exportToYaml([$page1, $page2, $page3]);
        $this->assertMatchesSnapshot($yaml, new ImportExportYamlDriver());
    }
}
