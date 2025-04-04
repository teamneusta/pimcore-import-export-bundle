<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Unit\Converter;

use Neusta\ConverterBundle\Converter;
use Neusta\Pimcore\ImportExportBundle\Converter\TypeStrategyConverter;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class TypeStrategyConverterTest extends TestCase
{
    use ProphecyTrait;

    private TypeStrategyConverter $strategyConverter;
    private Converter|ObjectProphecy $converterA;
    private Converter|ObjectProphecy $converterB;
    private Converter|ObjectProphecy $converterC;

    protected function setUp(): void
    {
        $this->converterA = $this->prophesize(Converter::class);
        $this->converterA->convert(Argument::any(), null)->willReturn(new A());
        $this->converterB = $this->prophesize(Converter::class);
        $this->converterB->convert(Argument::any(), null)->willReturn(new B());
        $this->converterC = $this->prophesize(Converter::class);
        $this->converterC->convert(Argument::any(), null)->willReturn(new C());
    }

    /**
     * @test
     */
    public function convert_regular_case(): void
    {
        $strategyConverter = new TypeStrategyConverter(
            [
                A::class => $this->converterA->reveal(),
                B::class => $this->converterB->reveal(),
                C::class => $this->converterC->reveal(),
            ]
        );
        $a = new A();
        $b = new B();
        $c = new C();

        $strategyConverter->convert($a);
        $this->converterA->convert($a, null)->shouldHaveBeenCalled();

        $strategyConverter->convert($b);
        $this->converterA->convert($b, null)->shouldHaveBeenCalled();

        $strategyConverter->convert($c);
        $this->converterA->convert($c, null)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function convert_regular_case_with_interitance_hierarchy(): void
    {
        $strategyConverter = new TypeStrategyConverter(
            [
                C::class => $this->converterC->reveal(),
                B::class => $this->converterB->reveal(),
                A::class => $this->converterA->reveal(),
            ]
        );
        $a = new A();
        $b = new B();
        $c = new C();

        $strategyConverter->convert($a);
        $this->converterA->convert($a, null)->shouldHaveBeenCalled();

        $strategyConverter->convert($b);
        $this->converterB->convert($b, null)->shouldHaveBeenCalled();

        $strategyConverter->convert($c);
        $this->converterC->convert($c, null)->shouldHaveBeenCalled();
    }

    /**
     * @test
     */
    public function convert_exceptional_case(): void
    {
        $strategyConverter = new TypeStrategyConverter(
            [
                C::class => $this->converterC->reveal(),
                B::class => $this->converterB->reveal(),
                A::class => $this->converterA->reveal(),
            ]
        );
        $d = new InvalidType();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No converter found for type Neusta\Pimcore\ImportExportBundle\Tests\Unit\Converter\InvalidType');
        $strategyConverter->convert($d);
    }
}

class A
{
}

class B extends A
{
}

class C extends B
{
}

class InvalidType
{
}
