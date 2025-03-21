<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Integration\Documents\Export;

use Neusta\Pimcore\ImportExportBundle\Assets\Export\AssetExporter;
use Neusta\Pimcore\ImportExportBundle\Tests\Integration\Documents\ImportExportYamlDriver;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Pimcore\Model\Asset\Image;
use Pimcore\Test\KernelTestCase;
use Spatie\Snapshots\MatchesSnapshots;

class AssetExporterTest extends KernelTestCase
{
    use MatchesSnapshots;
    use ResetDatabase;

    private AssetExporter $exporter;

    protected function setUp(): void
    {
        $this->exporter = self::getContainer()->get(AssetExporter::class);
    }

    public function testSinglePageExport(): void
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
}
