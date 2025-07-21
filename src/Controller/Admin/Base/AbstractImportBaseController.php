<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin\Base;

use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ImportRepositoryInterface;
use Pimcore\Model\Element\AbstractElement;
use Pimcore\Model\Element\DuplicateFullPathException;
use Psr\Log\LoggerInterface;
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

    /** @var array<int, int> */
    private array $resultStatistics;

    /**
     * @param ImportRepositoryInterface<TElement> $repository
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected ImportRepositoryInterface $repository,
        protected string $elementType = 'Element',
    ) {
        $this->resultStatistics = [
            self::SUCCESS_ELEMENT_REPLACEMENT => 0,
            self::SUCCESS_WITHOUT_REPLACEMENT => 0,
            self::SUCCESS_NEW_ELEMENT => 0,
            self::FAILURE_INCONSISTENCY => 0,
        ];
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
            $elements = $this->importByFile($file, $format);
            foreach ($elements as $element) {
                ++$this->resultStatistics[$this->replaceIfExists($element)];
            }
        } catch (\Exception $e) {
            return $this->createJsonResponse(false, $e->getMessage(), 500);
        } finally {
            try {
                $this->cleanUp();
            } catch (\Throwable $cleanupError) {
                $this->logger->warning($cleanupError->getMessage());
            }
        }

        return $this->createJsonResponse(true, $this->createResultMessage());
    }

    protected function createJsonResponse(bool $success, string $message, int $statusCode = 200): JsonResponse
    {
        return new JsonResponse(['success' => $success, 'message' => $message], $statusCode);
    }

    /**
     * @param TElement $element
     */
    protected function replaceIfExists(AbstractElement $element): int
    {
        $oldElement = $this->repository->getByPath('/' . $element->getFullPath());
        if (null !== $oldElement) {
            if ($this->overwrite) {
                if (null === $element->getId() || $oldElement->getId() === $element->getId()) {
                    $oldElement->delete();
                    $element->save(['versionNote' => 'overwritten by pimcore-import-export-bundle']);
                } else {
                    $this->logger->error(
                        \sprintf('Two pages with same key (%s) and path (%s) but different IDs (new ID: %d, old ID: %d) found. This seems to be an inconsistency of your importing data. Please check your import file.',
                            $element->getKey(),
                            $element->getPath(),
                            $element->getId(),
                            $oldElement->getId()
                        ));

                    return self::FAILURE_INCONSISTENCY;
                }

                return self::SUCCESS_ELEMENT_REPLACEMENT;
            }

            return self::SUCCESS_WITHOUT_REPLACEMENT;
        }
        $element->save(['versionNote' => 'added by pimcore-import-export-bundle']);

        return self::SUCCESS_NEW_ELEMENT;
    }

    protected function createResultMessage(): string
    {
        $resultMessage = '<table><tr><th>' . $this->elementType . '</th><th>Count</th></tr>';

        foreach ($this->resultStatistics as $resultCode => $result) {
            if ($result > 0) {
                $resultMessage .= '<tr><td>';
                $resultMessage .= $this->messagesMap[$resultCode] . '</td><td>' . $result . '</td></tr>';
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
    abstract protected function importByFile(UploadedFile $file, string $format): array;

    protected function cleanUp(): void
    {
        // implement clean ups in subclasses if necessary
    }
}
