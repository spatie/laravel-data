<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;

class ClosureLazy extends ConditionalLazy
{
    public function __construct(
        Closure $closure,
    ) {
        parent::__construct(fn () => true, $closure);
    }

    public function resolve(): Closure
    {
        return $this->value;
    }
}
