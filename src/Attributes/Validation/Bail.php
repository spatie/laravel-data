<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Bail extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'bail';
    }

    public function parameters(): array
    {
        return [];
    }
}
