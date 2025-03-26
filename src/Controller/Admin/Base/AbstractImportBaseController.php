<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin\Base;

use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\ImportRepositoryInterface;
use Pimcore\Model\Document;
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

    /**
     * @var string[] Map of error codes to messages
     */
    protected array $messagesMap = [
        self::ERR_NO_FILE_UPLOADED => 'No file uploaded',
        self::SUCCESS_ELEMENT_REPLACEMENT => 'replaced successfully',
        self::SUCCESS_WITHOUT_REPLACEMENT => 'not replaced',
        self::SUCCESS_NEW_ELEMENT => 'imported successfully',
    ];
    protected bool $overwrite = false;

    /**
     * @param ImportRepositoryInterface<TElement> $repository
     */
    public function __construct(
        protected ImportRepositoryInterface $repository,
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
            $elements = $this->importByFile($file, $format);
        } catch (\Exception $e) {
            return $this->createJsonResponse(false, $e->getMessage(), 500);
        }

        return $this->createJsonResponse(true, $this->createResultMessage($elements, Document::class));
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
                $oldElement->delete();
                $element->save(['versionNote' => 'overwritten by pimcore-import-export-bundle']);

                return self::SUCCESS_ELEMENT_REPLACEMENT;
            }

            return self::SUCCESS_WITHOUT_REPLACEMENT;
        }
        $element->save(['versionNote' => 'added by pimcore-import-export-bundle']);

        return self::SUCCESS_NEW_ELEMENT;
    }

    /**
     * @param array<TElement> $elements
     */
    protected function createResultMessage(array $elements, string $elementType): string
    {
        $results = [
            self::SUCCESS_ELEMENT_REPLACEMENT => 0,
            self::SUCCESS_WITHOUT_REPLACEMENT => 0,
            self::SUCCESS_NEW_ELEMENT => 0,
        ];
        foreach ($elements as $element) {
            if ($element instanceof $elementType) {
                $resultCode = $this->replaceIfExists($element);
                ++$results[$resultCode];
            }
        }

        $resultMessage = '';

        foreach ($results as $resultCode => $result) {
            if ($result > 0) {
                if (1 === $result) {
                    $start = 'One ' . $elementType;
                } else {
                    $start = \sprintf('%d ' . $elementType . 's', $result);
                }
                $message = \sprintf('%s %s', $start, $this->messagesMap[$resultCode]);
                $resultMessage .= $message . '<br/><br/>';
            }
        }

        return '<p style="padding: 20px;">' . $resultMessage . '</p>';
    }

    /**
     * @return array<TElement>
     *
     * @throws ConverterException
     * @throws DuplicateFullPathException
     */
    abstract protected function importByFile(UploadedFile $file, string $format): array;
}
