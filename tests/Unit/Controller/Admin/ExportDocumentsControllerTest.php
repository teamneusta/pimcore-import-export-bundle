<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Unit\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Controller\Admin\ExportDocumentsController;
use Neusta\Pimcore\ImportExportBundle\Documents\Export\PageExporter;
use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DocumentRepository;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\PageRepository;
use PHPUnit\Framework\TestCase;
use Pimcore\Model\Document\Page;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportDocumentsControllerTest extends TestCase
{
    use ProphecyTrait;

    private ExportDocumentsController $controller;

    /** @var ObjectProphecy<PageExporter> */
    private $exporter;

    /** @var ObjectProphecy<PageRepository> */
    private $documentRepository;

    private Request $request;

    protected function setUp(): void
    {
        $this->exporter = $this->prophesize(Exporter::class);
        $this->documentRepository = $this->prophesize(DocumentRepository::class);

        $this->controller = new ExportDocumentsController(
            $this->exporter->reveal(),
            $this->documentRepository->reveal(),
        );
        $this->request = new Request(['doc_id' => 17]);
    }

    public function testExportPage_regular_case(): void
    {
        $page = $this->prophesize(Page::class);
        $this->documentRepository->getById(17)->willReturn($page->reveal());
        $this->exporter->export([$page->reveal()], 'yaml')->willReturn('TEST_YAML');

        $response = $this->controller->export($this->request);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('application/yaml', $response->headers->get('Content-type'));
        self::assertSame('TEST_YAML', $response->getContent());
    }

    public function testExportPage_exceptional_case(): void
    {
        $page = $this->prophesize(Page::class);
        $this->documentRepository->getById(17)->willReturn($page->reveal());
        $this->exporter->export([$page->reveal()], 'yaml')->willThrow(new \Exception('Problem'));

        $response = $this->controller->export($this->request);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        self::assertSame('"Problem"', $response->getContent());
    }

    public function testExportPage_no_document_case(): void
    {
        $this->documentRepository->getById(17)->willReturn(null);

        $response = $this->controller->export($this->request);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        self::assertSame('"Document with id \u002217\u0022 was not found"', $response->getContent());
    }
}
