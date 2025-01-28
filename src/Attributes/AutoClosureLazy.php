<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;
use Closure;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Support\Lazy\ClosureLazy;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class AutoClosureLazy extends AutoLazy
{
    public function build(Closure $castValue, mixed $payload, DataProperty $property, mixed $value): ClosureLazy
    {
        return Lazy::closure(fn () => $castValue($value));
    }
}
