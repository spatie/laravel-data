<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Alpha extends StringValidationAttribute
{
    use GenericRule;
}
