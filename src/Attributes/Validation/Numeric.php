<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Numeric extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'numeric';
    }

    public function parameters(ValidationPath $path): array
    {
        return [];
    }
}
