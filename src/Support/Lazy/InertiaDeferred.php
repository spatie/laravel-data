<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;
use Inertia\DeferProp;

class InertiaDeferred extends ConditionalLazy
{
    public function __construct(
        Closure $value,
    ) {
        parent::__construct(fn () => true, $value);
    }

    public function resolve(): DeferProp
    {
        return new DeferProp($this->value);
    }
}
