<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IPv4 extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'ipv4';
    }

    public function parameters(): array
    {
        return [];
    }
}
