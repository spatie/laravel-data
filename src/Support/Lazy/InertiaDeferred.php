<?php

namespace Spatie\LaravelData\Support\Lazy;

use Inertia\DeferProp;

class InertiaDeferred extends ConditionalLazy
{
    public function __construct(
        mixed $value,
    ) {
        $callback = is_a($value, DeferProp::class)
            ? fn () => $value()
            : fn () => new DeferProp($value);

        parent::__construct(fn () => true, $callback);
    }

    public function resolve(): DeferProp
    {
        return new DeferProp($this->value);
    }
}
