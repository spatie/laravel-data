<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\LaravelData\Lazy;

class DefaultLazy extends Lazy
{
    protected function __construct(
        protected Closure $value
    ) {
    }

    public function resolve(): mixed
    {
        return ($this->value)();
    }

    public function __serialize(): array
    {
        return [
            'value' => new SerializableClosure($this->value),
            'defaultIncluded' => $this->defaultIncluded,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->value = $data['value']->getClosure();
        $this->defaultIncluded = $data['defaultIncluded'];
    }
}
