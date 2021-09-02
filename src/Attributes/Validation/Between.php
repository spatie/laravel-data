<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Between implements ValidationAttribute
{
    public function __construct(private int | float $min, private int | float $max)
    {
    }

    public function getRules(): array
    {
        return ["between:{$this->min},{$this->max}"];
    }
}
