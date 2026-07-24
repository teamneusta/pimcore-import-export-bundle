<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\EventSubscriber;

use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportEvent;
use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportStatus;
use Neusta\Pimcore\ImportExportBundle\Import\Registry\PendingTagsRegistry;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\TagRepository;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\Document;
use Pimcore\Model\Element\AbstractElement;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ImportTagsEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PendingTagsRegistry $registry,
        private readonly TagRepository $tagRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ImportEvent::class => 'onImportEvent',
        ];
    }

    public function onImportEvent(ImportEvent $event): void
    {
        if (!\in_array($event->getStatus(), [ImportStatus::Created, ImportStatus::Updated], true)) {
            return;
        }

        $newElement = $event->getNewElement();
        if (null === $newElement) {
            return;
        }

        $tags = $this->registry->consume($newElement->getFullPath());
        if (empty($tags)) {
            return;
        }

        // For UpdateExistingPageStrategy, $oldElement is saved in-place → use its ID.
        // For ReplaceExistingElementStrategy, $newElement is saved with a new ID → use its ID.
        $elementId = ($newElement->getId() ?: null) ?? $event->getOldElement()?->getId();
        if (null === $elementId || 0 === $elementId) {
            return;
        }

        $cType = $this->resolveCType($newElement);
        if (null === $cType) {
            return;
        }

        $this->tagRepository->setTagsForElement($cType, $elementId, $tags);
    }

    private function resolveCType(AbstractElement $element): ?string
    {
        return match (true) {
            $element instanceof Asset => TagRepository::CTYPE_ASSET,
            $element instanceof Document => TagRepository::CTYPE_DOCUMENT,
            $element instanceof AbstractObject => TagRepository::CTYPE_DATAOBJECT,
            default => null,
        };
    }
}
