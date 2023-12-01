<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Declined extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'declined';
    }

    public function parameters(): array
    {
        return [];
    }
}
