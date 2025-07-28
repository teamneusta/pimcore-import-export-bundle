<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class PrioritizedAttributesNormalizer implements NormalizerInterface
{
    private ObjectNormalizer $normalizer;

    /** @var array<string> */
    private array $priorities;

    /**
     * @param array<string> $priorities
     */
    public function __construct(ObjectNormalizer $normalizer, array $priorities = [])
    {
        $this->normalizer = $normalizer;
        $this->priorities = $priorities; // e.g.: ['type', 'id', 'parentId', 'path', ...]
    }

    /**
     * @param object               $object
     * @param array<string, mixed> $context
     */
    public function normalize($object, ?string $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);

        if (!\is_array($data)) {
            return $data;
        }

        $sorted = [];

        foreach ($this->priorities as $key) {
            if (\array_key_exists($key, $data)) {
                $sorted[$key] = $data[$key];
                unset($data[$key]);
            }
        }

        // Append all remaining properties at the end
        return array_merge($sorted, $data);
    }

    public function supportsNormalization($data, ?string $format = null): bool
    {
        return \is_object($data) && $this->normalizer->supportsNormalization($data, $format);
    }
}
