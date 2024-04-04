<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\LaravelData\Lazy;

class ConditionalLazy extends Lazy
{
    protected function __construct(
        protected Closure $condition,
        protected Closure $value,
    ) {
    }

    public function resolve(): mixed
    {
        return ($this->value)();
    }

    public function shouldBeIncluded(): bool
    {
        return (bool) ($this->condition)();
    }

    public function __serialize(): array
    {
        return [
            'condition' => new SerializableClosure($this->condition),
            'value' => new SerializableClosure($this->value),
            'defaultIncluded' => $this->defaultIncluded,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->condition = $data['condition']->getClosure();
        $this->value = $data['value']->getClosure();
        $this->defaultIncluded = $data['defaultIncluded'];
    }
}
