<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AlphaNumeric extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'alpha_num';
    }

    public function parameters(): array
    {
        return [];
    }
}
