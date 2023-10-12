<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class MacAddress extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'mac_address';
    }

    public function parameters(): array
    {
        return [];
    }
}
