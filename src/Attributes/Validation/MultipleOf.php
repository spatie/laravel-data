<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MultipleOf extends StringValidationAttribute
{
    use GenericRule;
    public function __construct(private int | float $value)
    {
    }

    public function parameters(): array
    {
        return [$this->value];
    }
}
