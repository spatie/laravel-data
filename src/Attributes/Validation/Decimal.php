<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Decimal extends StringValidationAttribute
{
    public function __construct(protected int|float|RouteParameterReference $min, protected null|int|float|RouteParameterReference $max)
    {
    }

    public static function keyword(): string
    {
        return 'decimal';
    }

    public function parameters(): array
    {
        return array_filter([$this->min, $this->max]);
    }
}
