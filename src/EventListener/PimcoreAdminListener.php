<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\EventListener;

use Pimcore\Event\BundleManager\PathsEvent;

final class PimcoreAdminListener
{
    public function addJSFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/neustapimcoreimportexport/js/startup.js',
            '/bundles/neustapimcoreimportexport/js/exportAsset.js',
            '/bundles/neustapimcoreimportexport/js/exportDocument.js',
            '/bundles/neustapimcoreimportexport/js/importDocument.js',
        ]);
    }

    public function addCSSFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/neustapimcoreimportexport/css/icons.css',
        ]);
    }
}
