<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin\Base;

use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Import\EventSubscriber\StatisticsEventSubscriber;
use Neusta\Pimcore\ImportExportBundle\Import\ParentRelationResolver;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ImportRepositoryInterface;
use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Element\DuplicateFullPathException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template TElement of AbstractElement
 */
abstract class AbstractImportBaseController
{
    public const ERR_NO_FILE_UPLOADED = 1;
    public const SUCCESS_ELEMENT_REPLACEMENT = 2;
    public const SUCCESS_WITHOUT_REPLACEMENT = 3;
    public const SUCCESS_NEW_ELEMENT = 4;
    public const FAILURE_INCONSISTENCY = 5;

    /**
     * @var string[] Map of error codes to messages
     */
    protected array $messagesMap = [
        self::ERR_NO_FILE_UPLOADED => 'No file uploaded',
        self::SUCCESS_ELEMENT_REPLACEMENT => 'replaced successfully',
        self::SUCCESS_WITHOUT_REPLACEMENT => 'not replaced',
        self::SUCCESS_NEW_ELEMENT => 'imported successfully',
        self::FAILURE_INCONSISTENCY => 'failed due to inconsistency in the data',
    ];

    protected bool $overwrite = false;

    /**
     * @param ImportRepositoryInterface<TElement> $repository
     */
    public function __construct(
        protected ApplicationLogger $applicationLogger,
        protected StatisticsEventSubscriber $statisticsEventSubscriber,
        protected ImportRepositoryInterface $repository,
        protected ParentRelationResolver $parentRelationResolver,
        protected string $elementType = 'Element',
    ) {
    }

    public function import(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            return $this->createJsonResponse(false, $this->messagesMap[self::ERR_NO_FILE_UPLOADED], 400);
        }

        $format = (string) $request->query->get('format', 'yaml');
        $this->overwrite = $request->request->getBoolean('overwrite');

        try {
            $this->importByFile($file, $format, true, $this->overwrite);
        } catch (\Throwable $e) {
            return $this->createJsonResponse(false, $e->getMessage(), 500);
        } finally {
            try {
                $this->cleanUp();
            } catch (\Throwable $cleanupError) {
                $this->applicationLogger->warning($cleanupError->getMessage());
            }
        }

        return $this->createJsonResponse(true, $this->createResultMessage($this->statisticsEventSubscriber->getStatistics()));
    }

    protected function createJsonResponse(bool $success, string $message, int $statusCode = 200): JsonResponse
    {
        return new JsonResponse(['success' => $success, 'message' => $message], $statusCode);
    }

    /**
     * @param array<string, int> $stats
     */
    protected function createResultMessage(array $stats): string
    {
        $resultMessage = '<table><tr><th>' . $this->elementType . '</th><th>Count</th></tr>';

        foreach ($stats as $resultCode => $result) {
            if ($result > 0) {
                $resultMessage .= '<tr><td>';
                $resultMessage .= $resultCode . '</td><td>' . $result . '</td></tr>';
            }
        }

        return $resultMessage . '</table>';
    }

    /**
     * @return array<TElement>
     *
     * @throws ConverterException
     * @throws DuplicateFullPathException
     */
    abstract protected function importByFile(UploadedFile $file, string $format, bool $forcedSave = true, bool $overwrite = false): array;

    protected function cleanUp(): void
    {
        // implement clean ups in subclasses if necessary
    }
}
