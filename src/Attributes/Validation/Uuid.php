<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Uuid implements ValidationAttribute
{
    public function getRules(): array
    {
        return ['uuid'];
    }
}
