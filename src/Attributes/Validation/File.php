<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class File extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'file';
    }

    public function parameters(): array
    {
        return [];
    }
}
