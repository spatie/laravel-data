<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Filled extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'filled';
    }

    public function parameters(): array
    {
        return [];
    }
}
