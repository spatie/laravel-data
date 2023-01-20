<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Image extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'image';
    }

    public function parameters(ValidationPath $path): array
    {
        return [];
    }
}
