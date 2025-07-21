<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests;

use Pimcore\Test\KernelTestCase;

class DummyKernelTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::getContainer();
    }

    /** @test */
    public function symfony_service_definitions_must_compile(): void
    {
        // If this test is passed, it means that the kernel could be loaded and there are no compiling errors in the
        // Symfony service definitions
        $this->expectNotToPerformAssertions();
    }
}
