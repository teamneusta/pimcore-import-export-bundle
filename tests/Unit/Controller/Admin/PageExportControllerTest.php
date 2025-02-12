<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Unit\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Controller\Admin\PageExportController;
use Neusta\Pimcore\ImportExportBundle\Documents\Export\PageExporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\PageRepository;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\Document\Page;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PageExportControllerTest extends TestCase
{
    use ProphecyTrait;

    private PageExportController $controller;

    /** @var ObjectProphecy<PageExporter> */
    private $pageExporter;

    /** @var ObjectProphecy<PageRepository> */
    private $pageRepository;

    protected function setUp(): void
    {
        $this->pageExporter = $this->prophesize(PageExporter::class);
        $this->pageRepository = $this->prophesize(PageRepository::class);

        $this->controller = new PageExportController(
            $this->pageExporter->reveal(),
            $this->pageRepository->reveal(),
        );
    }

    public function testExportPage_regular_case(): void
    {
        $request = $this->prophesize(Request::class);
        $request->get('page_id')->willReturn('17');

        $page = $this->prophesize(Page::class);
        $this->pageRepository->getById(17)->willReturn($page->reveal());
        $this->pageExporter->toYaml($page->reveal())->willReturn('TEST_YAML');

        $response = $this->controller->exportPage($request->reveal());

        self::assertTrue($response instanceof Response);
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertEquals('application/json', $response->headers->get('Content-type'));
        self::assertEquals('TEST_YAML', $response->getContent());
    }

    public function testExportPage_exceptional_case(): void
    {
        $request = $this->prophesize(Request::class);
        $request->get('page_id')->willReturn('17');

        $page = $this->prophesize(Page::class);
        $this->pageRepository->getById(17)->willReturn($page->reveal());
        $this->pageExporter->toYaml($page->reveal())->willThrow(new \Exception('Problem'));

        $response = $this->controller->exportPage($request->reveal());

        self::assertTrue($response instanceof JsonResponse);
        self::assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertEquals('"Problem"', $response->getContent());
    }

    public function testExportPage_no_document_case(): void
    {
        $request = $this->prophesize(Request::class);
        $request->get('page_id')->willReturn('17');

        $this->pageRepository->getById(17)->willReturn(null);

        $response = $this->controller->exportPage($request->reveal());

        self::assertTrue($response instanceof JsonResponse);
        self::assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertEquals('"Page with id \u002217\u0022 was not found"', $response->getContent());
    }
}
