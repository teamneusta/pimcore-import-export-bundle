<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\Event;

enum ImportStatus: string
{
    case SKIPPED = 'SKIPPED';
    case CREATED = 'CREATED';
    case UPDATED = 'UPDATED';
    case FAILED = 'FAILED';
}
