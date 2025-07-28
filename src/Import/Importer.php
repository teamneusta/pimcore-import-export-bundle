<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import;

use Neusta\ConverterBundle\Converter;
use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Exception\InconsistencyException;
use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportEvent;
use Neusta\Pimcore\ImportExportBundle\Import\Event\ImportStatus;
use Neusta\Pimcore\ImportExportBundle\Import\Strategy\MergeElementStrategy;
use Neusta\Pimcore\ImportExportBundle\Model\Element;
use Neusta\Pimcore\ImportExportBundle\Serializer\SerializerInterface;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ImportRepositoryInterface;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Element\DuplicateFullPathException;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @template TSource of \ArrayObject<int|string, mixed>
 * @template TTarget of AbstractElement
 */
class Importer
{
    /**
     * @param ServiceLocator<TTarget> $repositoryLocator
     * @param ServiceLocator<TTarget> $converterLocator
     * @param ServiceLocator<TTarget> $mergeStrategyLocator
     */
    public function __construct(
        private readonly ServiceLocator $repositoryLocator,
        private readonly ServiceLocator $converterLocator,
        private readonly ServiceLocator $mergeStrategyLocator,
        private readonly ParentRelationResolver $parentRelationResolver,
        private readonly SerializerInterface $serializer,
        private readonly EventDispatcherInterface $dispatcher,
    ) {
    }

    /**
     * @return array<TTarget>
     *
     * @throws InconsistencyException
     * @throws ConverterException
     * @throws DuplicateFullPathException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function import(string $yamlInput, string $format, bool $forcedSave, bool $overwrite): array
    {
        $config = $this->serializer->deserialize($yamlInput, $format);

        if (!\is_array($config) || !\is_array($config[Element::ELEMENTS] ?? null)) {
            throw new \DomainException(\sprintf('Given data in format %s is not valid.', $format));
        }

        $elements = [];

        foreach ($config[Element::ELEMENTS] as $element) {
            $result = null;
            $typeKey = key($element);

            $repository = $this->repositoryLocator->get($typeKey);
            $converter = $this->converterLocator->get($typeKey);
            $mergeStrategy = $this->mergeStrategyLocator->get($typeKey);

            if (
                $repository instanceof ImportRepositoryInterface
                && $converter instanceof Converter
                && $mergeStrategy instanceof MergeElementStrategy
            ) {
                /** @var AbstractElement $result */
                $result = $converter->convert(new \ArrayObject($element[$typeKey]));
                if ($forcedSave) {
                    $oldElement = $repository->getByPath($result->getFullPath());
                    if (!$oldElement) {
                        // New element - save it
                        $this->parentRelationResolver->resolve($result);
                        try {
                            $result->save(['versionNote' => 'created by pimcore-import-export-bundle']);
                            $this->dispatcher->dispatch(new ImportEvent(ImportStatus::CREATED, $typeKey, $element, $result, null));
                        } catch (\Exception $e) {
                            $this->dispatcher->dispatch(new ImportEvent(ImportStatus::FAILED, $typeKey, $element, $result, $oldElement, $e->getMessage()));
                        }
                    } elseif ($overwrite) {
                        if ($this->newElementHasNoValidId($result) || $this->bothHaveSameId($oldElement, $result)) {
                            // Update existing element by new one
                            try {
                                $mergeStrategy->mergeAndSave($oldElement, $result);
                                $this->dispatcher->dispatch(new ImportEvent(ImportStatus::UPDATED, $typeKey, $element, $result, $oldElement));
                            } catch (\Exception $e) {
                                $this->dispatcher->dispatch(new ImportEvent(ImportStatus::FAILED, $typeKey, $element, $result, $oldElement, $e->getMessage()));
                            }
                        } else {
                            $this->dispatcher->dispatch(new ImportEvent(
                                ImportStatus::INCONSISTENCY, $typeKey, $element, $result, $oldElement,
                                <<<ERR_MESSAGE
                                Two elements with same key (%s) and path (%s) but different IDs (new ID: %d, old ID: %d) found.
                                This seems to be an inconsistency of your importing data. Please check your import file.

                                To enable overwriting of existing elements, the new element should have no or the same ID as the old element.
                                ERR_MESSAGE
                            ));
                        }
                    } else {
                        // Don't overwrite existing element
                        $this->dispatcher->dispatch(new ImportEvent(ImportStatus::SKIPPED, $typeKey, $element, $result, $oldElement));
                    }
                }
            }
            if ($result) {
                $elements[] = $result;
            }
        }

        /* @var array<TTarget> $elements */
        return $elements; // @phpstan-ignore-line
    }

    private function newElementHasNoValidId(AbstractElement $result): bool
    {
        return null === $result->getId() || 0 === $result->getId();
    }

    private function bothHaveSameId(AbstractElement $oldElement, AbstractElement $result): bool
    {
        return $oldElement->getId() === $result->getId();
    }
}
