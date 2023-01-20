<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Nullable extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'nullable';
    }

    public function parameters(ValidationPath $path): array
    {
        return [];
    }
}
