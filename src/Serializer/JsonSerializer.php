<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Serializer;

use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

class JsonSerializer implements SerializerInterface
{
    public function __construct(
        private readonly SymfonySerializerInterface $serializer,
    ) {
    }

    public function serialize(mixed $data, string $format): string
    {
        return $this->serializer->serialize($data, 'json', [
            'json_encode_options' => \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE,
        ]);
    }

    public function deserialize(string $data, string $format): mixed
    {
        $result = json_decode($data, true);

        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('JSON deserialization error: ' . json_last_error_msg());
        }

        return $result;
    }
}
