<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
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
