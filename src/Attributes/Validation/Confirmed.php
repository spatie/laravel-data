<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Confirmed extends ValidationAttribute
{
    public function getRules(): array
    {
        return ['confirmed'];
    }
}
