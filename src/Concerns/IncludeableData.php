<?php

namespace Spatie\LaravelData\Concerns;

use Closure;
use Spatie\LaravelData\Support\Partials\ForwardsToPartialsDefinition;
use Spatie\LaravelData\Support\Partials\PartialsDefinition;
use Spatie\LaravelData\Support\PartialsParser;
use Spatie\LaravelData\Support\PartialTrees;

trait IncludeableData
{
    use ForwardsToPartialsDefinition;

    protected function getPartialsDefinition(): PartialsDefinition
    {
        return $this->getDataContext()->partialsDefinition;
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
