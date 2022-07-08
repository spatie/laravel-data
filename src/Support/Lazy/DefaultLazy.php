<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;
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
}
