<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Support\Partials\ForwardsToPartialsDefinition;
use Spatie\LaravelData\Support\Partials\Partial;
use Spatie\LaravelData\Support\Partials\PartialsDefinition;
use SplObjectStorage;

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
