<?php

namespace Spatie\LaravelData\Concerns;

trait ExcludeableData
{
    protected array $except = [];

    public function excludeWhen(): array
    {
        return [];
    }

    public function except(...$except): static
    {
        $this->except = array_merge($this->except, $except);

        return $this;
    }

    public function getExcludedData(): array
    {
        $exclusions = array_keys(array_filter(
            $this->excludeWhen(),
            fn ($value) => $value instanceof \Closure
                ? ($value)($this)
                : $value
        ));

        return array_merge($exclusions, $this->except);
    }
}
