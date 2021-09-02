<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Url implements ValidationAttribute
{
    public function getRules(): array
    {
        return ['url'];
    }
}
