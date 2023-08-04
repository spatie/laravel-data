<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Present extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'present';
    }

    public function parameters(): array
    {
        return [];
    }
}
