<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Max extends StringValidationAttribute
{
    public function __construct(protected int|RouteParameterReference $value)
    {
    }

    public static function keyword(): string
    {
        return 'max';
    }

    public function parameters(): array
    {
        return [$this->value];
    }
}
