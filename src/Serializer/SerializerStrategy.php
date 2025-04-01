<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Serializer;

class SerializerStrategy implements SerializerInterface
{
    /**
     * @param array<string, SerializerInterface> $formatToSerializerMap
     */
    public function __construct(
        private array $formatToSerializerMap,
    ) {
    }

    public function serialize(mixed $data, string $format): string
    {
        if (\array_key_exists($format, $this->formatToSerializerMap)) {
            return $this->formatToSerializerMap[$format]->serialize($data, $format);
        }
        throw new \InvalidArgumentException('No serializer found for format ' . $format);
    }

    public function deserialize(string $data, string $format): mixed
    {
        if (\array_key_exists($format, $this->formatToSerializerMap)) {
            return $this->formatToSerializerMap[$format]->deserialize($data, $format);
        }
        throw new \InvalidArgumentException('No de-serializer found for format ' . $format);
    }
}
