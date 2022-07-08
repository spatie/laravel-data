<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Date extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'date';
    }

    public function parameters(): array
    {
        return [];
    }
}
