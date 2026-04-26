<?php

namespace Spatie\LaravelData\Support\Lazy;

use Closure;
use Inertia\LazyProp;
use Inertia\OptionalProp;

class InertiaLazy extends ConditionalLazy
{
    public function __construct(
        Closure $value,
    ) {
        parent::__construct(fn () => true, $value);
    }

    public function resolve(): LazyProp|OptionalProp
    {
        // Prefer LazyProp on Inertia v2 to preserve consumer instanceof checks; LazyProp was removed in v3.
        if (class_exists(LazyProp::class)) {
            return new LazyProp($this->value);
        }

        return new OptionalProp($this->value);
    }
}
