<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

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
