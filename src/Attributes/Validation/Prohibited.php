<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Prohibited implements ValidationAttribute
{
    public function getRules(): array
    {
        return ['prohibited'];
    }
}
