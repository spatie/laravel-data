<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IP extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'ip';
    }

    public function parameters(): array
    {
        return [];
    }
}
