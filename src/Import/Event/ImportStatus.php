<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Import\Event;

enum ImportStatus: string
{
    case Skipped = 'SKIPPED';
    case Created = 'CREATED';
    case Updated = 'UPDATED';
    case Inconsistency = 'INCONSISTENCY';
    case Failed = 'FAILED';
}
