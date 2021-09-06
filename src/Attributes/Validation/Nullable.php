<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Nullable extends ValidationAttribute
{
    public function getRules(): array
    {
        return ['nullable'];
    }
}
