<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Uuid extends StringValidationAttribute
{
    use GenericRule;
}
