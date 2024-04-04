<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ListType extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'list';
    }

    public function parameters(): array
    {
        return [];
    }
}
