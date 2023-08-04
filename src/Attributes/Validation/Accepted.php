<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Accepted extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'accepted';
    }

    public function parameters(): array
    {
        return [];
    }
}
