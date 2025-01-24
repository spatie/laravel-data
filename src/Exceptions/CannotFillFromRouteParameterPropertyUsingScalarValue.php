<?php

namespace Spatie\LaravelData\Exceptions;

use Exception;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\InjectsPropertyValue;
use Spatie\LaravelData\Support\DataProperty;

class CannotFillFromRouteParameterPropertyUsingScalarValue extends Exception
{
    public static function create(DataProperty $property, InjectsPropertyValue $attribute): self
    {
        $attribute = Str::afterLast($attribute::class, '\\');

        return new self("Attribute {$attribute} cannot be used with injected scalar parameters for property {$property->className}::{$property->name}");
    }
}
