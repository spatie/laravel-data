<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Json extends ValidationAttribute
{
    public function getRules(): array
    {
        return ['json'];
    }
}
