<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\EventListener;

use Pimcore\Event\BundleManager\PathsEvent;

final class PimcoreAdminListener
{
    public function addJSFiles(PathsEvent $event): void
    {
        $event->setPaths(array_merge(
            $event->getPaths(),
            [
                '/bundles/neustapimcoreimportexport/js/exportPage.js',
            ],
        ));
    }
}
