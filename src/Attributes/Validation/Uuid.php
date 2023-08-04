<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Uuid extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'uuid';
    }

    public function parameters(): array
    {
        return [];
    }
}
