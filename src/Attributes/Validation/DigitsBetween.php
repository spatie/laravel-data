<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DigitsBetween implements ValidationAttribute
{
    public function __construct(private int $min, private int $max)
    {
    }

    public function getRules(): array
    {
        return ["digits_between:{$this->min},{$this->max}"];
    }
}
