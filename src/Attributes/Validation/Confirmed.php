<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Confirmed extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'confirmed';
    }

    public function parameters(ValidationPath $path): array
    {
        return [];
    }
}
