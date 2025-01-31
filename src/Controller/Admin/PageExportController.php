<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Documents\Export\PageExporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\PageRepository;
use Pimcore\Controller\UserAwareController;
use Pimcore\Model\Document\Page;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PageExportController extends UserAwareController
{
    public function __construct(
        private PageExporter $pageExporter,
        private PageRepository $pageRepository,
    ) {
    }

    /**
     * @Route("/admin/page/export", name="page_export", methods={"GET"})
     */
    public function exportPage(Request $request): Response
    {
        $pageId = $request->get('page_id');
        $page = $this->pageRepository->getById($pageId);

        if (!$page instanceof Page) {
            return new JsonResponse(
                \sprintf('Page with id "%s" was not found', $pageId),
                Response::HTTP_NOT_FOUND,
            );
        }

        try {
            $json = $this->pageExporter->toYaml($page);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response = new Response($json);
        $response->headers->set('Content-type', 'application/json');
        $response->headers->set(
            'Content-Disposition',
            HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $this->createFilename($page)),
        );

        return $response;
    }

    private function createFilename(Page $page): string
    {
        return \sprintf('%s.yaml', str_replace(' ', '_', (string) $page->getKey()));
    }
}
