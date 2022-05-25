<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Nullable extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'nullable';
    }

    public function parameters(): array
    {
        return [];
    }
}
