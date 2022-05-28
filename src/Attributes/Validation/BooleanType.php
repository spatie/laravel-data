<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BooleanType extends StringValidationAttribute
{
    use GenericRule;


    public static function keyword(): string
    {
        return 'boolean';
    }
}
