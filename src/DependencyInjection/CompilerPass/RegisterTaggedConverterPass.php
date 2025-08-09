<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\DependencyInjection\CompilerPass;

use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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

            $targetType = $this->determineTargetType($definition, $arguments);
            $definition->addTag('neusta.import_export.converter', ['type' => $targetType]);
        }
    }

    private function determineTargetType(Definition $definition, array $arguments): string
    {
        if (\array_key_exists('target', $arguments) && \is_string($arguments['target'])) {

            return $arguments['target'];
        }
        if (\array_key_exists('target_factory', $arguments) && \is_string($arguments['target_factory'])) {
            $reflection = new ReflectionClass($arguments['target_factory']);

            $method = $reflection->getMethod('create');
            return $method->getReturnType()->getName();
        }
        return '';
    }
}
