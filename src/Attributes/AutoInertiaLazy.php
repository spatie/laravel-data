<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Closure;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Lazy\InertiaLazy;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class AutoInertiaLazy extends AutoLazy
{
    public function build(Closure $castValue, mixed $payload, DataProperty $property, mixed $value): InertiaLazy
    {
        return Lazy::inertia(fn () => $castValue($value));
    }
}
