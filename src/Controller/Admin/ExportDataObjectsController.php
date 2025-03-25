<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Export\Exporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DataObjectRepository;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DocumentRepository;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Document;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ExportDataObjectsController
{
    public function __construct(
        private Exporter             $exporter,
        private DataObjectRepository $objectRepository,
    ) {
    }

    #[Route(
        '/admin/neusta/import-export/objects/export',
        name: 'neusta_pimcore_import_export_objects_export',
        methods: ['GET']
    )]
    public function export(Request $request): Response
    {
        $object = $this->objectRepository->getById($request->query->getInt('object_id'));
        if (!$object instanceof Concrete) {
            return new JsonResponse(
                \sprintf('Data Object with id "%s" was not found', $request->query->getInt('object_id')),
                Response::HTTP_NOT_FOUND,
            );
        }

        return $this->exportObjects([$object], $request->query->getString('filename'), 'yaml');
    }

    #[Route(
        '/admin/neusta/import-export/objects/export/with-children',
        name: 'neusta_pimcore_import_export_objects_export_with_children',
        methods: ['GET']
    )]
    public function exportWithChildren(Request $request): Response
    {
        $object = $this->objectRepository->getById($request->query->getInt('object_id'));
        if (!$object instanceof Concrete) {
            return new JsonResponse(
                \sprintf('Data Object with id "%s" was not found', $request->query->getInt('object_id')),
                Response::HTTP_NOT_FOUND,
            );
        }

        $objects = $this->objectRepository->findAllObjectsWithChildren($object);

        return $this->exportObjects($objects, $request->query->getString('filename'), $request->query->getString('format'));
    }

    /**
     * @param iterable<Document> $documents
     */
    private function exportObjects(iterable $documents, string $filename, string $format): Response
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
