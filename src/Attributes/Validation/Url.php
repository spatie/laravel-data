<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Url extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'url';
    }

    public function parameters(ValidationPath $path): array
    {
        return [];
    }
}
