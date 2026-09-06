<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Ulid extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'ulid';
    }

    public function parameters(): array
    {
        return [];
    }
}
