<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;
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
}
