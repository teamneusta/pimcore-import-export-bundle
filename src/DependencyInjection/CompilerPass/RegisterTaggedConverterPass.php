<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\DependencyInjection\CompilerPass;

use Neusta\ConverterBundle\TargetFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterTaggedConverterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $converterDefs = $container->getExtensionConfig('neusta_converter');
        $flattenedConverterDefs = [];

        foreach ($converterDefs as $entry) {
            if (isset($entry['converter']) && \is_array($entry['converter'])) {
                $flattenedConverterDefs = array_merge($flattenedConverterDefs, $entry['converter']);
            }
        }
        foreach ($flattenedConverterDefs as $serviceId => $arguments) {
            if (!$container->hasDefinition($serviceId)) {
                continue;
            }
            $definition = $container->getDefinition($serviceId);

            $targetType = $this->determineTargetType($container, $arguments);
            if ($targetType) {
                $definition->addTag('neusta.import_export.converter', ['type' => $targetType]);
            }
        }
    }

    /**
     * @param array<string, string> $arguments
     */
    private function determineTargetType(Container $container, array $arguments): ?string
    {
        if (\array_key_exists('target', $arguments) && \is_string($arguments['target'])) {
            return $arguments['target'];
        }
        if (\array_key_exists('target_factory', $arguments) && \is_string($arguments['target_factory'])) {
            $factory = $container->get($arguments['target_factory']);
            if ($factory instanceof TargetFactory) {
                return \get_class($factory->create());
            }
        }

        return null;
    }
}
