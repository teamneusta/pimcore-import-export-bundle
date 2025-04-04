<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Integration\Import;

use Neusta\Pimcore\ImportExportBundle\Import\ParentRelationResolver;
use Neusta\Pimcore\TestingFramework\Database\ResetDatabase;
use Pimcore\Model\Document\Page;
use Pimcore\Test\KernelTestCase;

class ParentRelationResolverTest extends KernelTestCase
{
    use ResetDatabase;

    private ParentRelationResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = self::getContainer()->get(ParentRelationResolver::class);
    }

    /**
     * @test
     */
    public function resolve_regular_case_parent_id(): void
    {
        $element = new Page();
        $element->setParentId(1);
        $this->resolver->resolve($element);

        self::assertEquals('/', $element->getPath());
    }

    /**
     * @test
     */
    public function resolve_regular_case_path(): void
    {
        $element = new Page();
        $element->setPath('/');
        $this->resolver->resolve($element);

        self::assertEquals(1, $element->getParentId());
    }

    /**
     * @test
     */
    public function resolve_regular_case_parent(): void
    {
        $parent = new Page();
        $parent->setParentId(1);
        $parent->setKey('parent');
        $parent->save();

        $element = new Page();
        $element->setParentId($parent->getId());
        $element->setKey('element');
        $this->resolver->resolve($element);
        self::assertEquals('/parent/', $element->getPath());
    }
}
