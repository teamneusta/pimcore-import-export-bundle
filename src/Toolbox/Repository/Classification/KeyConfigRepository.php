<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\Classification;

use Pimcore\Model\DataObject\Classificationstore\KeyConfig;

class KeyConfigRepository
{
    public function getById(int $id): ?KeyConfig
    {
        return KeyConfig::getById($id);
    }

    public function getByName(string $name, int $storeId = 1, bool $force = false): ?KeyConfig
    {
        return KeyConfig::getByName($name, $storeId, $force);
    }
}
