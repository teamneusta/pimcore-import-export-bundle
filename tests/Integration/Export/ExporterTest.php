<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Integration\Documents\Export;

use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Tests\Integration\Documents\ImportExportYamlDriver;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\Document\Editable\Input;
use Pimcore\Model\Document\Page;
use Pimcore\Test\KernelTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class ExporterTest extends KernelTestCase
{
    use MatchesSnapshots;
    use ResetDatabase;

    private Exporter $exporter;

    protected function setUp(): void
    {
        $this->exporter = self::getContainer()->get(Exporter::class);
    }

    public function test_single_image_export(): void
    {
        $asset = new Image();
        $asset->setId(999);
        $asset->setParentId(1);
        $asset->setKey('image_1');
        $asset->setType("image");
        $asset->setPath('/');

        $yaml = $this->exporter->export([$asset], 'yaml');
        $this->assertMatchesSnapshot($yaml, new ImportExportYamlDriver());
    }

    public function test_single_page_export(): void
    {
        $page = $this->createPageWithInputEditable();

        $yaml = $this->exporter->export([$page], 'yaml');
        $this->assertMatchesSnapshot($yaml, new ImportExportYamlDriver());
    }

    public function test_single_page_export_json(): void
    {
        $page = $this->createPageWithInputEditable();

        $json = $this->exporter->export([$page], 'json');
        $this->assertMatchesJsonSnapshot($json);
    }

    public function test_simple_saved_pages_export(): void
    {
        $page1 = $this->createSimplePage('1', 1, '/');
        $page2 = $this->createSimplePage('2', 1, '/');

        $page1->save();
        $page2->save();

        $yaml = $this->exporter->export([$page1, $page2], 'yaml');
        $this->assertMatchesSnapshot($yaml, new ImportExportYamlDriver());
    }

    public function test_simple_unsaved_pages_export(): void
    {
        $page1 = $this->createSimplePage('1', 1, '/will/not/overwritten/');
        $page2 = $this->createSimplePage('2', 1, '/will/not/overwritten/');

        $yaml = $this->exporter->export([$page1, $page2], 'yaml');
        $this->assertMatchesSnapshot($yaml, new ImportExportYamlDriver());
    }

    public function test_tree_pages_export(): void
    {
        $page1 = $this->createSimplePage('1', 1, '/will/be/overwritten/');
        $page1->save();

        $page2 = $this->createSimplePage('2', $page1->getId(), '/will/be/overwritten/');
        $page2->save();

        $page3 = $this->createSimplePage('3', $page2->getId(), '/will/be/overwritten/');
        $page3->save();

        $yaml = $this->exporter->export([$page1, $page2, $page3], 'yaml');
        $this->assertMatchesSnapshot($yaml, new ImportExportYamlDriver());
    }

    /**
     * @return Page
     */
    private function createPageWithInputEditable(): Page
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
        return $page;
    }

    /**
     * @param string $index
     * @param $parentId
     * @param string $path
     * @return Page
     */
    private function createSimplePage(string $index, int $parentId, string $path): Page
    {
        $page1 = new Page();
        $page1->setParentId($parentId);
        $page1->setPath($path);
        $page1->setKey('test_document_' . $index);
        $page1->setTitle('Test Document_' . $index);
        return $page1;
    }

}
