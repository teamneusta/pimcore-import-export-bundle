<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Documents\Export\PageExporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\PageRepository;
use Pimcore\Model\Document\Page;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageExportController
{
    public function __construct(
        private PageExporter $pageExporter,
        private PageRepository $pageRepository,
    ) {
    }

    #[Route(
        '/admin/neusta/import-export/page/export',
        name: 'neusta_pimcore_import_export_page_export',
        methods: ['GET']
    )]
    public function exportPage(Request $request): Response
    {
        try {
            $page = $this->getPageByRequest($request);
        } catch (\Exception $exception) {
            return new JsonResponse(
                \sprintf('Page with id "%s" was not found', $exception->getMessage()),
                Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $yaml = $this->pageExporter->exportToYaml([$page]);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->createJsonResponseByYaml($yaml, $this->createFilename($page));
    }

    #[Route(
        '/admin/neusta/import-export/page/export/with-children',
        name: 'neusta_pimcore_import_export_page_export_with_children',
        methods: ['GET']
    )]
    public function exportPageWithChildren(Request $request): Response
    {
        try {
            $page = $this->getPageByRequest($request);
        } catch (\Exception $exception) {
            return new JsonResponse(
                \sprintf('Page with id "%s" was not found', $exception->getMessage()),
                Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $yaml = $this->pageExporter->exportToYaml($this->pageRepository->findAllPagesWithSubPages($page));
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->createJsonResponseByYaml($yaml, $this->createFilename($page));
    }

    private function getPageByRequest(Request $request): Page
    {
        $pageId = $request->query->getInt('page_id');
        $page = $this->pageRepository->getById($pageId);

        if (!$page instanceof Page) {
            throw new \Exception((string) $pageId);
        }

        return $page;
    }

    private function createFilename(Page $page): string
    {
        return \sprintf('%s.yaml', str_replace(' ', '_', (string) $page->getKey()));
    }

    private function createJsonResponseByYaml(string $yaml, string $filename): Response
    {
        $response = new Response($yaml);
        $response->headers->set('Content-type', 'application/yaml');
        $response->headers->set(
            'Content-Disposition',
            HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $filename),
        );

        return $response;
    }
}
