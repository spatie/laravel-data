<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ActiveUrl extends StringValidationAttribute
{
    use GenericRule;

}
