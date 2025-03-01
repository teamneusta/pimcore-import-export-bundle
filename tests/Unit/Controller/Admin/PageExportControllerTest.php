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

    private Request $request;

    protected function setUp(): void
    {
        $this->pageExporter = $this->prophesize(PageExporter::class);
        $this->pageRepository = $this->prophesize(PageRepository::class);

        $this->controller = new PageExportController(
            $this->pageExporter->reveal(),
            $this->pageRepository->reveal(),
        );
        $this->request = new Request(['page_id' => 17]);
    }

    public function testExportPage_regular_case(): void
    {
        $page = $this->prophesize(Page::class);
        $this->pageRepository->getById(17)->willReturn($page->reveal());
        $this->pageExporter->exportToYaml($page->reveal())->willReturn('TEST_YAML');

        $response = $this->controller->exportPage($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('application/yaml', $response->headers->get('Content-type'));
        self::assertSame('TEST_YAML', $response->getContent());
    }

    public function testExportPage_exceptional_case(): void
    {
        $page = $this->prophesize(Page::class);
        $this->pageRepository->getById(17)->willReturn($page->reveal());
        $this->pageExporter->exportToYaml($page->reveal())->willThrow(new \Exception('Problem'));

        $response = $this->controller->exportPage($this->request);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertSame('"Problem"', $response->getContent());
    }

    public function testExportPage_no_document_case(): void
    {
        $this->pageRepository->getById(17)->willReturn(null);

        $response = $this->controller->exportPage($this->request);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame('"Page with id \u002217\u0022 was not found"', $response->getContent());
    }
}
