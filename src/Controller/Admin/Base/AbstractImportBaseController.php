<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Controller\Admin\Base;

use Neusta\ConverterBundle\Exception\ConverterException;
use Neusta\Pimcore\ImportExportBundle\Import\Importer;
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

    /**
     * @param Importer<\ArrayObject<int|string, mixed>, TElement> $importer
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

        $overwrite = $request->request->getBoolean('overwrite');
        $format = (string)$request->query->get('format', 'yaml');

        try {
            $elements = $this->importByFile($file, $format);
        } catch (\Exception $e) {
            return $this->createJsonResponse(false, $e->getMessage(), 500);
        }

        return $this->createJsonResponse(true, $this->createResultMessage($elements, $overwrite, Document::class));
    }

    protected function createJsonResponse(bool $success, string $message, int $statusCode = 200): JsonResponse
    {
        return new JsonResponse(['success' => $success, 'message' => $message], $statusCode);
    }

    /**
     * @param TElement $element
     */
    protected function replaceIfExists(AbstractElement $element, bool $overwrite): int
    {
        $oldElement = $this->repository->getByPath('/' . $element->getFullPath());
        if (null !== $oldElement) {
            if ($overwrite) {
                $oldElement->delete();
                $element->save();

                return self::SUCCESS_ELEMENT_REPLACEMENT;
            }

            return self::SUCCESS_WITHOUT_REPLACEMENT;
        }
        $element->save();

        return self::SUCCESS_NEW_ELEMENT;
    }

    /**
     * @param array $elements
     * @param bool $overwrite
     * @param string $elementType
     * @return string
     */
    protected function createResultMessage(array $elements, bool $overwrite, string $elementType): string
    {
        $results = [
            self::SUCCESS_ELEMENT_REPLACEMENT => 0,
            self::SUCCESS_WITHOUT_REPLACEMENT => 0,
            self::SUCCESS_NEW_ELEMENT => 0,
        ];
        foreach ($elements as $element) {
            if ($element instanceof $elementType) {
                $resultCode = $this->replaceIfExists($element, $overwrite);
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
     * @param UploadedFile $file
     * @param string $format
     * @return AbstractElement[]
     * @throws ConverterException
     * @throws DuplicateFullPathException
     */
    protected abstract function importByFile(UploadedFile $file, string $format): array;
}
