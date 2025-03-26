<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Controller\Admin\Base\AbstractImportBaseController;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DataObjectRepository;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractImportBaseController<Concrete>
 */
final class ImportDataObjectsController extends AbstractImportBaseController
{
    /**
     * @param Importer<\ArrayObject<int|string, mixed>, Concrete> $importer
     */
    public function __construct(
        DataObjectRepository $repository,
        private Importer $importer,
    ) {
        parent::__construct($repository);
    }

    #[Route(
        '/admin/neusta/import-export/object/import',
        name: 'neusta_pimcore_import_export_object_import',
        methods: ['POST']
    )]
    public function import(Request $request): JsonResponse
    {
        return parent::import($request);
    }

    protected function importByFile(UploadedFile $file, string $format): array
    {
        return $this->importer->import($file->getContent(), $format);
    }
}
