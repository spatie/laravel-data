<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AlphaDash extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'alpha_dash';
    }

    public function parameters(): array
    {
        return [];
    }
}
