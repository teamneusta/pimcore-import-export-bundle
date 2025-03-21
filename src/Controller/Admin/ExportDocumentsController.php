<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DocumentRepository;
use Pimcore\Model\Document;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExportDocumentsController
{
    public function __construct(
        private Exporter $exporter,
        private DocumentRepository $documentRepository,
    ) {
    }

    #[Route(
        '/admin/neusta/import-export/documents/export',
        name: 'neusta_pimcore_import_export_documents_export',
        methods: ['GET']
    )]
    public function export(Request $request): Response
    {
        $document = $this->documentRepository->getById($request->query->getInt('doc_id'));
        if (!$document instanceof Document) {
            return new JsonResponse(
                \sprintf('Document with id "%s" was not found', $request->query->getInt('doc_id')),
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->exportDocuments([$document], $request->query->getString('filename'), 'yaml');
    }

    #[Route(
        '/admin/neusta/import-export/documents/export/with-children',
        name: 'neusta_pimcore_import_export_documents_export_with_children',
        methods: ['GET']
    )]
    public function exportWithChildren(Request $request): Response
    {
        $document = $this->documentRepository->getById($request->query->getInt('doc_id'));
        if (!$document instanceof Document) {
            return new JsonResponse(
                \sprintf('Document with id "%s" was not found', $request->query->getInt('doc_id')),
                Response::HTTP_NOT_FOUND,
            );
        }

        $documents = $this->documentRepository->findAllDocsWithChildren($document);

        return $this->exportDocuments($documents, $request->query->getString('filename'), $request->query->getString('format'));
    }

    /**
     * @param iterable<Document> $documents
     */
    private function exportDocuments(iterable $documents, string $filename, string $format): Response
    {
        try {
            $yaml = $this->exporter->export($documents, $format);
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
