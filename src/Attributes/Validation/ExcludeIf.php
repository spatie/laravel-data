<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\Concerns\BuildsValidationRules;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcludeIf extends ValidationAttribute
{


    public function __construct(private string $field, private string|int|float|bool $value)
    {
    }

    public function getRules(): array
    {
        return ["exclude_if:{$this->field},{$this->normalizeValue($this->value)}"];
    }
}
