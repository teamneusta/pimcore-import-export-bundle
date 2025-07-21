<?php declare(strict_types=1);

use Neusta\Pimcore\TestingFramework\Pimcore\BootstrapPimcore;

require_once __DIR__ . '/../vendor/autoload.php';

BootstrapPimcore::bootstrap(
    PIMCORE_PROJECT_ROOT: __DIR__ . '/app',
    KERNEL_CLASS: TestKernel::class,
);
