<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Positive extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'gt';
    }

    public function parameters(): array
    {
        return [0];
    }
}
