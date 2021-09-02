<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\In as BaseIn;

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
