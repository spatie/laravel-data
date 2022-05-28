<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IP extends StringValidationAttribute
{
    use GenericRule;

    public static function keyword(): string
    {
        return 'ip';
    }
}
