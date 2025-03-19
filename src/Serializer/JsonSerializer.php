<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Serializer;

use Neusta\Pimcore\ImportExportBundle\Documents\Model\Page;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;

class JsonSerializer implements SerializerInterface
{
    private SymfonySerializerInterface $jsonSerializer;

    public function __construct(
    ) {
        $this->jsonSerializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    public function serialize(mixed $data, string $format): string
    {
        return $this->jsonSerializer->serialize($data, 'json');
    }

    public function deserialize(string $data, string $format): mixed
    {
        return json_decode($data, true);
    }
}
