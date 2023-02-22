<?php

namespace Spatie\LaravelData\Support\Partials;

use Closure;

trait ForwardsToPartialsDefinition
{
    abstract protected function getPartialsDefinition(): PartialsDefinition;

    public function include(string ...$includes): static
    {
        foreach ($includes as $include) {
            $this->getPartialsDefinition()->includes[$include] = true;
        }

        return $this;
    }

    public function exclude(string ...$excludes): static
    {
        foreach ($excludes as $exclude) {
            $this->getPartialsDefinition()->excludes[$exclude] = true;
        }

        return $this;
    }

    public function only(string ...$only): static
    {
        foreach ($only as $onlyDefinition) {
            $this->getPartialsDefinition()->only[$onlyDefinition] = true;
        }

        return $this;
    }

    public function except(string ...$except): static
    {
        foreach ($except as $exceptDefinition) {
            $this->getPartialsDefinition()->except[$exceptDefinition] = true;
        }

        return $this;
    }

    public function includeWhen(string $include, bool|Closure $condition): static
    {
        $this->getPartialsDefinition()->includes[$include] = $condition;

        return $this;
    }

    public function excludeWhen(string $exclude, bool|Closure $condition): static
    {
        $this->getPartialsDefinition()->excludes[$exclude] = $condition;

        return $this;
    }

    public function onlyWhen(string $only, bool|Closure $condition): static
    {
        $this->getPartialsDefinition()->only[$only] = $condition;

        return $this;
    }

    public function exceptWhen(string $except, bool|Closure $condition): static
    {
        $this->getPartialsDefinition()->except[$except] = $condition;

        return $this;
    }
}
