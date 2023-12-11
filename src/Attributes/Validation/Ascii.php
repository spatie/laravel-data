<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Ascii extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'ascii';
    }

    public function parameters(): array
    {
        return [];
    }
}
