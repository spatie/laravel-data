<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IntegerType extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'integer';
    }

    public function parameters(): array
    {
        return [];
    }
}
