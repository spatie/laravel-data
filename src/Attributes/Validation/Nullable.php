<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Nullable extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'nullable';
    }

    public function parameters(): array
    {
        return [];
    }
}
