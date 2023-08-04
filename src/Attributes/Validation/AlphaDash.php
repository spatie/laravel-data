<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class AlphaDash extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'alpha_dash';
    }

    public function parameters(): array
    {
        return [];
    }
}
