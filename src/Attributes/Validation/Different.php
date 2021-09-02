<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Different implements ValidationAttribute
{
    public function __construct(private string $field)
    {
    }

    public function getRules(): array
    {
        return ['different:{$this->field}'];
    }
}
