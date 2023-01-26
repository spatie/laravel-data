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

        foreach ($this->_additional as $name => $value) {
            $additional[$name] = $value instanceof Closure
                ? ($value)($this)
                : $value;
        }

        return $additional;
    }
}
