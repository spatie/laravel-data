<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Same implements ValidationAttribute
{
    public function __construct(private string $fieldName)
    {
    }

    public function getRules(): array
    {
        return ['same:' . $this->fieldName];
    }
}
