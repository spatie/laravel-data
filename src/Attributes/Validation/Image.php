<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Image extends ValidationAttribute
{
    public function getRules(): array
    {
        return ['image'];
    }
}
