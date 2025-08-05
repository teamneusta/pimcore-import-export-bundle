<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle;

use Neusta\ConverterBundle\NeustaConverterBundle;
use Neusta\Pimcore\ImportExportBundle\DependencyInjection\CompilerPass\RegisterTaggedConverterPass;
use Pimcore\Bundle\ApplicationLoggerBundle\PimcoreApplicationLoggerBundle;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Pimcore\HttpKernel\Bundle\DependentBundleInterface;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class NeustaPimcoreImportExportBundle extends AbstractPimcoreBundle implements DependentBundleInterface
{
    use PackageVersionTrait;

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterTaggedConverterPass());
    }

    public static function registerDependentBundles(BundleCollection $collection): void
    {
        $collection->addBundle(NeustaConverterBundle::class);
        $collection->addBundle(PimcoreApplicationLoggerBundle::class);
    }
}
