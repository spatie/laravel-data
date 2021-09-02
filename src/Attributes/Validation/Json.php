<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Json implements ValidationAttribute
{
    public function getRules(): array
    {
        return ['json'];
    }
}
