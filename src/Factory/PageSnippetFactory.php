<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Factory;

use Neusta\ConverterBundle\TargetFactory;
use Pimcore\Model\Document\Page;
use Pimcore\Model\Document\PageSnippet;

/**
 * @template TContext of object
 *
 * @implements TargetFactory<PageSnippet, TContext|null>
 */
class PageSnippetFactory implements TargetFactory
{
    public function create(?object $ctx = null): PageSnippet
    {
        $pageSnippet = new Page();
        $pageSnippet->setType('snippet');

        return $pageSnippet;
    }
}
