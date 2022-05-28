<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Between extends StringValidationAttribute
{
    use GenericRule;

    public function __construct(private int|float $min, private int|float $max)
    {
    }

    public function parameters(): array
    {
        return [$this->min, $this->max];
    }
}
