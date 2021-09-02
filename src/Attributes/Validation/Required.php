<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Required implements ValidationAttribute
{
    public function getRules(): array
    {
        return ['required'];
    }
}
