<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Numeric extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'numeric';
    }

    public function parameters(): array
    {
        return [];
    }
}
