<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Different extends StringValidationAttribute
{
    use GenericRule;
    public function __construct(private string $field)
    {
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
