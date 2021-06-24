<?php

namespace Spatie\LaravelData\Concerns;

use Closure;

trait AppendableData
{
    protected array $additional = [];

    public function with(): array
    {
        return [];
    }

    public function additional(array $additional): static
    {
        $this->additional = array_merge($this->additional, $additional);

        return $this;
    }

    public function getAdditionalData(): array
    {
        $additional = $this->with();

        foreach ($this->additional as $name => $value) {
            $additional[$name] = $value instanceof Closure
                ? ($value)($this)
                : $value;
        }

        return $additional;
    }
}
