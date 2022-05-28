<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateFormat extends StringValidationAttribute
{
    use GenericRule;

    public function __construct(private string $format)
    {
    }

    public function parameters(): array
    {
        return [$this->format];
    }
}
