<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotRegex extends StringValidationAttribute
{
    use GenericRule;

    public function __construct(private string $pattern)
    {
    }

    public function parameters(): array
    {
        return [$this->pattern];
    }
}
