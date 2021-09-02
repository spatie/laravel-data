<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcludeUnless implements ValidationAttribute
{
    public function __construct(private string $field, private string $value)
{}

    public function getRules(): array
    {
        return ["exclude_unless:{$this->field},{$this->value}"];
    }
}
