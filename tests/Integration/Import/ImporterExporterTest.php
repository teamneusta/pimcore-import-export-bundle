<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Integration\Documents\Import;

use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Pimcore\Model\Asset;
use Pimcore\Model\Document\Page;
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

        $document = new Page();
        $document->setParentId(1);
        $document->setPath('/');
        $document->setKey('Text für viele');
        $document->save();
    }

    public function testImportExport_regular_case(): void
    {
        $yamlToImport = file_get_contents(__DIR__ . '/../data/Text Editor.yaml');
        $this->importer->import($yamlToImport, 'yaml', true, true);
        $document = Page::getByPath('/Test-Import-Export');
        $yamlExported = $this->exporter->export([$document], 'yaml');

        self::assertEquals($yamlToImport, $yamlExported);
    }
}
