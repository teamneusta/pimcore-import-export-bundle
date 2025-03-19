<?php

declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Serializer;

interface SerializerInterface
{
    public function serialize(mixed $data, string $format): string;

    public function deserialize(string $data, string $format): mixed;
}
