<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Closure;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Lazy\InertiaDeferred;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class AutoInertiaDeferred extends AutoLazy
{
    public function build(Closure $castValue, mixed $payload, DataProperty $property, mixed $value): InertiaDeferred
    {
        return Lazy::inertiaDeferred(fn () => $castValue($value));
    }
}
