<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Sometimes extends StringValidationAttribute
{
    use GenericRule;

}
