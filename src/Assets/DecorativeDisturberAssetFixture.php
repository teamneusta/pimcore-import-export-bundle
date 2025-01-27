<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Assets;

use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\AssetRepository;

class DecorativeDisturberAssetFixture extends Base\AbstractAssetFixture
{
    public const DISTURBER_MARKER = 'disturber_decorative';

    public function __construct(
        AssetRepository $assetRepository,
    ) {
        parent::__construct(
            $assetRepository,
            __DIR__ . '/images/stoerer_apollo_decorative.svg',
            'stoerer_decorative.svg',
            '/',
            self::DISTURBER_MARKER,
        );
    }
}
