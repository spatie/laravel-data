<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Negative extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'lt';
    }

    public function parameters(): array
    {
        return [0];
    }
}
