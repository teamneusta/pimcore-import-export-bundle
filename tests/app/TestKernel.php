<?php declare(strict_types=1);

use Neusta\Pimcore\ImportExportBundle\NeustaPimcoreImportExportBundle;
use Neusta\Pimcore\TestingFramework\Kernel\TestKernel as TestingFrameworkTestKernel;
use Pimcore\Bundle\ApplicationLoggerBundle\PimcoreApplicationLoggerBundle;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;

class TestKernel extends TestingFrameworkTestKernel
{
    public function registerBundlesToCollection(BundleCollection $collection): void
    {
        $collection->addBundle(new PimcoreApplicationLoggerBundle());
        $collection->addBundle(new NeustaPimcoreImportExportBundle());
    }
}
