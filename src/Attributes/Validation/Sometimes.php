<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Sometimes extends ValidationAttribute
{
    public function getRules(): array
    {
        return ['sometimes'];
    }
}
