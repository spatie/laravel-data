<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\Partials\ForwardsToPartialsDefinition;

trait IncludeableData
{
    use ForwardsToPartialsDefinition;

    protected function getPartialsContainer(): object
    {
        return $this->getDataContext();
    }

    protected function includeProperties(): array
    {
        return [];
    }

    protected function excludeProperties(): array
    {
        return [];
    }

    protected function onlyProperties(): array
    {
        return [];
    }

    protected function exceptProperties(): array
    {
        return [];
    }
}
