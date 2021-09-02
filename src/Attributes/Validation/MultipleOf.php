<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MultipleOf implements ValidationAttribute
{
    public function __construct(private int $value)
    {
    }

    public function getRules(): array
    {
        return ['multiple_of:' . $this->value];
    }
}
