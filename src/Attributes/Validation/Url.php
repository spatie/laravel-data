<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Url extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'url';
    }

    public function parameters(): array
    {
        return [];
    }
}
