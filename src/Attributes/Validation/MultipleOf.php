<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MultipleOf extends ValidationAttribute
{
    public function __construct(private int | float $value)
    {
    }

    public function getRules(): array
    {
        return ["multiple_of:{$this->value}"];
    }
}
