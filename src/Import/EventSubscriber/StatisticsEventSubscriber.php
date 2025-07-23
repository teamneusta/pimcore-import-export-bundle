<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\EventSubscriber;

use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportEvent;
use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatisticsEventSubscriber implements EventSubscriberInterface
{
    /** @var array<string, int> */
    protected static array $statistics = [];

    public static function getSubscribedEvents()
    {
        return [
            ImportEvent::class => [
                ['countStatistics', 0],
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    public static function getStatistics(): array
    {
        return self::$statistics;
    }

    public function countStatistics(ImportEvent $event): void
    {
        $this->incrementCounter($event->getStatus());
    }

    public function incrementCounter(ImportStatus $status): void
    {
        if (\array_key_exists($status->value, self::$statistics)) {
            ++self::$statistics[$status->value];
        } else {
            self::$statistics[$status->value] = 1;
        }
    }
}
