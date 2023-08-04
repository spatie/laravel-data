<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Ulid extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'ulid';
    }

    public function parameters(): array
    {
        return [];
    }
}
