<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IPv6 extends StringValidationAttribute
{
    use GenericRule;

    public static function keyword(): string
    {
        return 'ipv6';
    }
}
