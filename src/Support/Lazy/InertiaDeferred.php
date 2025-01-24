<?php

namespace Spatie\LaravelData\Support\Lazy;

use Inertia\DeferProp;

class InertiaDeferred extends ConditionalLazy
{
    public function __construct(
        DeferProp $value,
    ) {
        parent::__construct(fn () => true, fn () => $value());
    }

    public function resolve(): DeferProp
    {
        return new DeferProp($this->value);
    }
}
