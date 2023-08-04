<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class BooleanType extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'boolean';
    }

    public function parameters(): array
    {
        return [];
    }
}
