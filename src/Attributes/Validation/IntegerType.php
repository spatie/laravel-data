<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IntegerType implements ValidationAttribute
{
    public function getRules(): array
    {
        return ['integer'];
    }
}
