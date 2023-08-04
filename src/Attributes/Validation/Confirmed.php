<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Confirmed extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'confirmed';
    }

    public function parameters(): array
    {
        return [];
    }
}
