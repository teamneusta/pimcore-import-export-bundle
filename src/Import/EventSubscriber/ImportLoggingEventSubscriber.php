<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\EventSubscriber;

use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportEvent;
use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportStatus;
use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ImportLoggingEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ApplicationLogger $applicationLogger,
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            //            ImportEvent::class => [
            //                ['logImportEvent', 100],
            //            ],
        ];
    }

    public function logImportEvent(ImportEvent $event): void
    {
        if (\in_array(
            $event->getStatus(),
            [ImportStatus::INCONSISTENCY, ImportStatus::FAILED]
        )) {
            $this->writeApplicationError($event);
        } else {
            $this->writeApplicationLog($event);
        }
    }

    private function writeApplicationLog(ImportEvent $event): void
    {
        $prefix = \sprintf('[%s]', $event->getStatus()->value);
        $key = $event->getNewElement()?->getKey() ?? 'N/A';
        $path = $event->getNewElement()?->getPath() ?? 'N/A';
        $id = $event->getNewElement()?->getId() ?? 'N/A';

        $this->applicationLogger->info(
            <<<MESSAGE
            $prefix:
                type: {$event->getType()}
                key:  {$key}
                path: {$path}
                id:   {$id}
            MESSAGE,
            [
                'relatedObject' => $event->getOldElement() ?? 'N/A',
                'component' => 'Pimcore Import Export Bundle',
            ]
        );
    }

    private function writeApplicationError(ImportEvent $event): void
    {
        $prefix = \sprintf('[%s]', $event->getStatus()->value);
        $key = $event->getNewElement()?->getKey() ?? 'N/A';
        $path = $event->getNewElement()?->getPath() ?? 'N/A';
        $newId = $event->getNewElement()?->getId() ?? 'N/A';
        $oldId = $event->getOldElement()?->getId() ?? 'N/A';
        $errorMessage = $event->getErrorMessage() ?? 'N/A';

        $this->applicationLogger->error(
            <<<MESSAGE
            Error: {$errorMessage}

            $prefix:
                type: {$event->getType()}
                key:  {$key}
                path: {$path}
                id (new element):   {$newId}
                id (old element):   {$oldId}
            MESSAGE,
            [
                'relatedObject' => $event->getOldElement() ?? 'N/A',
                'component' => 'Pimcore Import Export Bundle',
            ]
        );
    }
}
