<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class StringType extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'string';
    }

    public function parameters(): array
    {
        return [];
    }
}
