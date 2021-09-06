<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AlphaNumeric extends ValidationAttribute
{
    public function getRules(): array
    {
        return ['alpha_num'];
    }
}
