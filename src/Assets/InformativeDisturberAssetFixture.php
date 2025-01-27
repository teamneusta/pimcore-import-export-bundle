<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Assets;

use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\AssetRepository;

class InformativeDisturberAssetFixture extends Base\AbstractAssetFixture
{
    public const DISTURBER_MARKER = 'disturber_informative';

    public function __construct(
        AssetRepository $assetRepository,
    ) {
        parent::__construct(
            $assetRepository,
            __DIR__ . '/images/stoerer_apollo_informative.svg',
            'stoerer_informative.svg',
            '/',
            self::DISTURBER_MARKER,
        );
    }
}
