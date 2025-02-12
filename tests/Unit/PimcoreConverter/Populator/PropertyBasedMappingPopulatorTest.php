<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Unit\PimcoreConverter\Populator;

use Neusta\ConverterBundle\Exception\PopulationException;
use Neusta\Pimcore\ImportExportBundle\PimcoreConverter\Context\ContextWithLocale;
use Neusta\Pimcore\ImportExportBundle\PimcoreConverter\Populator\PropertyBasedMappingPopulator;
use Neusta\Pimcore\ImportExportBundle\Tests\Unit\PimcoreConverter\Fixture\TestDataObject;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\DataObject;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @internal
 *
 * @coversNothing
 */
class PropertyBasedMappingPopulatorTest extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy|TestDataObject $target;
    private DataObject|ObjectProphecy $source;
    private ContextWithLocale|ObjectProphecy $context;
    private PropertyBasedMappingPopulator $populator;

    protected function setUp(): void
    {
        $this->target = new TestDataObject();
        $this->source = $this->prophesize(DataObject::class);
        $this->context = $this->prophesize(ContextWithLocale::class);
        $this->populator = new PropertyBasedMappingPopulator('testProperty', 'language');
    }

    /** @test */
    public function populateShouldPopulateTargetWithDataFromSourceWithoutLanguage(): void
    {
        $this->source->getProperty('language')->willReturn('de')->shouldBeCalled();
        $this->populator->populate($this->target, $this->source->reveal(), $this->context->reveal());
    }

    /** @test */
    public function populateWithException(): void
    {
        $this->expectException(PopulationException::class);
        $this->expectExceptionMessage('Population Exception (property["language"] -> testProperty): No property key language');

        $exception = new \RuntimeException('No property key language');
        $this->source->getProperty('language')->willThrow($exception)->shouldBeCalled();
        $this->populator->populate($this->target, $this->source->reveal(), $this->context->reveal());
    }
}
