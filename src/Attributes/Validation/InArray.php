<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class InArray implements ValidationAttribute
{
    public function __construct(private string $field)
    {
    }

    public function getRules(): array
    {
        return ["in_array:{$this->field}"];
    }
}
