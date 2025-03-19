<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Serializer;

use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;
use Symfony\Component\Yaml\Yaml;

class YamlSerializer implements SerializerInterface {

    public const YAML_DUMP_FLAGS =
        Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE |
        Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK |
        Yaml::DUMP_NULL_AS_TILDE;


    public function __construct(
        private readonly SymfonySerializerInterface $serializer,
    ) {
    }

    public function serialize(mixed $data, string $format): string
    {
        return $this->serializer->serialize($data, 'yaml', [
            'yaml_inline' => 6,
            'yaml_indent' => 0,
            'yaml_flags' => self::YAML_DUMP_FLAGS,
        ]);
    }

    public function deserialize(string $data, string $format): mixed
    {
        return Yaml::parse($data);
    }
}
