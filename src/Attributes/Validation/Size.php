<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Size extends StringValidationAttribute
{
    use GenericRule;

    public function __construct(private int $size)
    {
    }

    public function parameters(): array
    {
        return [$this->size];
    }
}
