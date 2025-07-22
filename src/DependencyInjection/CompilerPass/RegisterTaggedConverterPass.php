<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
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
            $definition = $container->getDefinition($serviceId);

            if (\array_key_exists('target', $arguments)) {
                $definition->addTag('neusta.import_export.converter', ['type' => $arguments['target']]);
            }
        }
    }
}
