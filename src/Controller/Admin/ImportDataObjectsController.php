<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin;

use Neusta\Pimcore\ImportExportBundle\Controller\Admin\Base\AbstractImportBaseController;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\DataObjectRepository;
use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @extends AbstractImportBaseController<DataObject>
 */
final class ImportDataObjectsController extends AbstractImportBaseController
{
    /**
     * @param Importer<\ArrayObject<int|string, mixed>, DataObject> $importer
     */
    public function __construct(
        DataObjectRepository $repository,
        private Importer $importer,
    ) {
        parent::__construct($repository, 'DataObject');
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
        try {
            $content = $file->getContent();

            return $this->importer->import($content, $format);
        } catch (\Exception $e) {
            throw new \Exception('Error reading uploaded file: ' . $e->getMessage(), 0, $e);
        }
    }
}
