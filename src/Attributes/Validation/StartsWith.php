<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\Concerns\BuildsValidationRules;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StartsWith extends ValidationAttribute
{
    public function __construct(private string|array $values)
    {
    }

    public function getRules(): array
    {
        return ["starts_with:{$this->normalizeValue($this->values)}"];
    }
}
