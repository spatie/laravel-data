<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcludeUnless extends StringValidationAttribute
{
    use GenericRule;
    public function __construct(private string $field, private string | bool | int | float $value)
    {
    }


    public function parameters(): array
    {
        return [
            $this->field,
            $this->normalizeValue($this->value),
        ];
    }
}
