<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class MultipleOf extends StringValidationAttribute
{
    public function __construct(protected int|float|ExternalReference $value)
    {
    }

    public static function keyword(): string
    {
        return 'multiple_of';
    }

    public function parameters(): array
    {
        return [$this->value];
    }
}
