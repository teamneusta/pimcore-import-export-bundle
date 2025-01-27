<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\Documents\Base;

use Neusta\Pimcore\FixtureBundle\Fixture\AbstractFixture;
use Neusta\Pimcore\ImportExportBundle\Documents\Import\PageImporter;
use Neusta\Pimcore\ImportExportBundle\Toolbox\Repository\PageRepository;
use Pimcore\Model\Document\Page;

abstract class AbstractPageFixture extends AbstractFixture
{
    /** @var array<string, string> */
    private array $params = [];

    public function __construct(
        protected PageImporter $pageImporter,
        protected PageRepository $pageRepository,
        protected string $fullqualifiedYamlFilename,
    ) {
    }

    public function create(): void
    {
        $this->internalCreate();
    }

    /**
     * @param array<string, string> $params
     */
    protected function createWithParams(array $params): void
    {
        $this->params = $params;
        $this->internalCreate();
    }

    protected function replaceIfExists(Page $page): void
    {
        $oldPage = $this->pageRepository->getByPath('/' . $page->getFullPath());
        if (null !== $oldPage) {
            $oldPage->delete();
        }
        $page->save();
    }

    private function internalCreate(): void
    {
        $yamlContent = $this->pageImporter->readYamlFileAndSetParameters(
            $this->fullqualifiedYamlFilename,
            $this->params,
        );
        $page = $this->pageImporter->parseYaml($yamlContent, false);
        $this->replaceIfExists($page);
    }
}
