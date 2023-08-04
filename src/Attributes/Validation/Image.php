<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Image extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'image';
    }

    public function parameters(): array
    {
        return [];
    }
}
