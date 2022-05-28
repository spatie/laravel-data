<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Max extends StringValidationAttribute
{
    use GenericRule;
    public function __construct(private int $value)
    {
    }

    public function parameters(): array
    {
        return [$this->value];
    }
}
