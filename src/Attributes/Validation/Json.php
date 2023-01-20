<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Json extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'json';
    }

    public function parameters(): array
    {
        return [];
    }
}
