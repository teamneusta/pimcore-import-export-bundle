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
        if (ImportStatus::FAILED === $event->getStatus()) {
            $this->writeApplicationError($event);
        } else {
            $this->writeApplicationLog($event);
        }
    }

    private function writeApplicationLog(ImportEvent $event): void
    {
        $prefix = \sprintf('[%s]', $event->getStatus()->value);

        $this->applicationLogger->info(
            <<<MESSAGE
            $prefix:
                type: {$event->getType()}
                key:  {$event->getNewElement()?->getKey()}
                path: {$event->getNewElement()?->getPath()}
                id:   {$event->getNewElement()?->getId()}
            MESSAGE,
            [
                'relatedObject' => $event->getOldElement(),
                'component' => 'Pimcore Import Export Bundle',
            ]
        );
    }

    private function writeApplicationError(ImportEvent $event): void
    {
        $this->applicationLogger->error(
            \sprintf('Two %ss with same key (%s) and path (%s) but different IDs (new ID: %d, old ID: %d) found. This seems to be an inconsistency of your importing data. Please check your import file.',
                $event->getType(),
                $event->getNewElement()?->getKey(),
                $event->getNewElement()?->getPath(),
                $event->getNewElement()?->getId(),
                $event->getOldElement()?->getId(),
            ),
            [
                'relatedObject' => $event->getOldElement(),
                'component' => 'Pimcore Import Export Bundle',
            ]
        );
    }
}
