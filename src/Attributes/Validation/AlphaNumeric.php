<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AlphaNumeric implements ValidationAttribute
{
    public function getRules(): array
    {
        return ['alpha_num'];
    }
}
