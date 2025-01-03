<?php

namespace Spatie\LaravelData\Concerns;

use Closure;

trait AppendableData
{
    protected array $_additional = [];

    public function with(): array
    {
        return [];
    }

    public function additional(array $additional): static
    {
        $this->_additional = array_merge($this->_additional, $additional);

        return $this;
    }

    public function getAdditionalData(): array
    {
        $additional = $this->with();

        $computedAdditional = array_map(
            fn ($value) => $value instanceof Closure ? ($value)($this) : $value,
            $additional
        );

        foreach ($this->_additional as $name => $value) {
            $computedAdditional[$name] = $value instanceof Closure
                ? ($value)($this)
                : $value;
        }

        return $computedAdditional;
    }
}
