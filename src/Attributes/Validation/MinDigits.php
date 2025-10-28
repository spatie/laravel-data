<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class MinDigits extends StringValidationAttribute
{
    public function __construct(protected int|ExternalReference $value)
    {
    }

    public static function keyword(): string
    {
        return 'min_digits';
    }

    public function parameters(): array
    {
        return [$this->value];
    }
}
