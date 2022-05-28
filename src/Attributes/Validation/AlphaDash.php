<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AlphaDash extends StringValidationAttribute
{
    use GenericRule;
}
