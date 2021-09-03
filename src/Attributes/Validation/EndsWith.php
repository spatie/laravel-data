<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Attributes\Validation\Concerns\BuildsValidationRules;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EndsWith implements ValidationAttribute
{
    use BuildsValidationRules;

    public function __construct(private string | array $values)
    {
    }

    public function getRules(): array
    {
        return ["ends_with:{$this->normalizeValue($this->values)}"];
    }
}
