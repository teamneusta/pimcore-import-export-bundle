<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\EventListener;

use Pimcore\Event\BundleManager\PathsEvent;

final class PimcoreAdminListener
{
    public function addJSFiles(PathsEvent $event): void
    {
        $event->addPaths([
            '/bundles/neustapimcoreimportexport/js/exportPage.js',
        ]);
    }
}
