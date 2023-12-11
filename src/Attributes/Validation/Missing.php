<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Missing extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'missing';
    }

    public function parameters(): array
    {
        return [];
    }
}
