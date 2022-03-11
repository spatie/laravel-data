<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;
use Spatie\LaravelData\Lazy;

class ConditionalLazy extends Lazy
{
    protected function __construct(
        private Closure $condition,
        private Closure $value,
    ) {
    }

    public function resolve(): mixed
    {
        return ($this->value)();
    }

    public function shouldBeIncluded(): bool
    {
        return ($this->condition)();
    }
}
