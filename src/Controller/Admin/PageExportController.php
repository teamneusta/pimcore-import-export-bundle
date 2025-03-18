<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Documents\Export\PageExporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\PageRepository;
use Pimcore\Model\Document\Page as PimcorePage;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $page = $this->getPageByRequest($request);
        if (!$page instanceof PimcorePage) {
            return new JsonResponse(
                \sprintf('Page with id "%s" was not found', $request->query->getInt('page_id')),
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->exportPages([$page], $request->query->getString('filename'));
    }

    #[Route(
        '/admin/neusta/import-export/page/export/with-children',
        name: 'neusta_pimcore_import_export_page_export_with_children',
        methods: ['GET']
    )]
    public function exportPageWithChildren(Request $request): Response
    {
        $page = $this->getPageByRequest($request);
        if (!$page instanceof PimcorePage) {
            return new JsonResponse(
                \sprintf('Page with id "%s" was not found', $request->query->getInt('page_id')),
                Response::HTTP_NOT_FOUND,
            );
        }

        $pages = $this->pageRepository->findAllPagesWithSubPages($page);

        return $this->exportPages($pages, $request->query->getString('filename'));
    }

    private function getPageByRequest(Request $request): ?PimcorePage
    {
        $pageId = $request->query->getInt('page_id');

        return $this->pageRepository->getById($pageId);
    }

    /**
     * @param iterable<PimcorePage> $pages
     */
    private function exportPages(iterable $pages, string $filename): Response
    {
        try {
            $yaml = $this->pageExporter->exportToYaml($pages);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->createJsonResponseByYaml($yaml, $filename);
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
