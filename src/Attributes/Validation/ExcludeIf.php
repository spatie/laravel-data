<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\Concerns\BuildsValidationRules;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcludeIf implements ValidationAttribute
{
    use BuildsValidationRules;

    public function __construct(private string $field, private string $value)
    {
    }

    public function getRules(): array
    {
        return ["exclude_if:{$this->field},{$this->normalizeValue($this->value)}"];
    }
}
