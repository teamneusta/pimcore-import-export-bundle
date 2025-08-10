<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Integration\Import;

use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Pimcore\Model\Asset;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Document\Snippet;
use Pimcore\Model\Version;
use Pimcore\Test\KernelTestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Spatie\Snapshots\MatchesSnapshots;

class ImporterExporterTest extends KernelTestCase
{
    use MatchesSnapshots;
    use ProphecyTrait;
    use ResetDatabase;

    private Importer $importer;
    private Exporter $exporter;

    protected function setUp(): void
    {
        Version::disable();
        $this->importer = self::getContainer()->get(Importer::class);
        $this->exporter = self::getContainer()->get(Exporter::class);

        $asset = new Asset();
        $asset->setParentId(1);
        $asset->setPath('/');
        $asset->setKey('logo_desktop.svg');
        $asset->save();
        self::assertNotNull($asset->getId(), 'Asset should be saved successfully');

        $document = new Page();
        $document->setParentId(1);
        $document->setPath('/');
        $document->setKey('Text für viele');
        $document->save();
        self::assertNotNull($document->getId(), 'Page should be saved successfully');
    }

    public function testImportExport_regular_case(): void
    {
        $yamlToImport = file_get_contents(__DIR__ . '/../data/Text Editor.yaml');
        $this->importer->import($yamlToImport, 'yaml', true, true);
        $document = Page::getByPath('/Test-Import-Export');
        $yamlExported = $this->exporter->export([$document], 'yaml');

        self::assertEquals($yamlToImport, $yamlExported);
    }

    public function testImportExport_regular_case_page_snippet(): void
    {
        $yamlToImport = file_get_contents(__DIR__ . '/../data/Page Snippet.yaml');
        $this->importer->import($yamlToImport, 'yaml', true, true);
        $document = Snippet::getByPath('/Page Snippet');
        $yamlExported = $this->exporter->export([$document], 'yaml');

        self::assertEquals($yamlToImport, $yamlExported);
    }
}
