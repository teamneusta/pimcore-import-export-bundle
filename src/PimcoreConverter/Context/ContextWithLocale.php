<?php declare(strict_types=1);

namespace Neusta\Pimcore\ImportExportBundle\PimcoreConverter\Context;

interface ContextWithLocale
{
    public function getLocale(): ?string;

    public function setLocale(?string $locale): static;
}
