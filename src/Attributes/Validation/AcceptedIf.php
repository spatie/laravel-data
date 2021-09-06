<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AcceptedIf extends ValidationAttribute
{
    public function __construct(private string $field, private string | bool | int | float $value)
    {
    }

    public function getRules(): array
    {
        $value = $this->value;

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        return ["accepted_if:{$this->field},{$value}"];
    }
}
