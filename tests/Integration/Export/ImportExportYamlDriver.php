<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Tests\Integration\Export;

use PHPUnit\Framework\Assert;
use Spatie\Snapshots\Driver;

class ImportExportYamlDriver implements Driver
{
    public function serialize($data): string
    {
        if (!\is_string($data)) {
            throw new \InvalidArgumentException('Only strings can be serialized to json');
        }

        return $data;
    }

    public function extension(): string
    {
        return 'yaml';
    }

    public function match($expected, $actual)
    {
        Assert::assertEquals($expected, $actual);
    }
}
