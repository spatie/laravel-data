<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Closure;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataProperty;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class AutoLazy
{
    public function build(Closure $castValue, mixed $payload, DataProperty $property, mixed $value): Lazy
    {
        return Lazy::create(fn () => $castValue($value));
    }
}
