<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredIf implements ValidationAttribute
{
    private array $values;

    public function __construct(private string $field, string ...$values)
    {
        $this->values = $values;
    }

    public function getRules(): array
    {
        return [
            "required_if:{$this->field}," . implode(',', $this->values),
        ];
    }
}
