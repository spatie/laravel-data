<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DigitsBetween extends StringValidationAttribute
{
    use GenericRule;
    public function __construct(private int $min, private int $max)
    {
    }


    public function parameters(): array
    {
        return [$this->min, $this->max];
    }
}
