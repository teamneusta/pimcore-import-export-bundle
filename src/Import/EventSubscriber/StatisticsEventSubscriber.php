<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\EventSubscriber;

use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportEvent;
use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StatisticsEventSubscriber implements EventSubscriberInterface
{
    /** @var array<string, int> */
    private array $statistics = [];

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
    public function getStatistics(): array
    {
        return $this->statistics;
    }

    public function countStatistics(ImportEvent $event): void
    {
        $this->incrementCounter($event->getStatus());
    }

    private function incrementCounter(ImportStatus $status): void
    {
        if (\array_key_exists($status->value, $this->statistics)) {
            ++$this->statistics[$status->value];
        } else {
            $this->statistics[$status->value] = 1;
        }
    }
}
