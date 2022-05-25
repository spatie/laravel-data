<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IPv6 extends StringValidationAttribute
{
    public static function keyword(): string
    {
        return 'ipv6';
    }

    public function parameters(): array
    {
        return [];
    }
}
