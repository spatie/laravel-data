<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredIf extends ValidationAttribute
{
    public function __construct(
        private string $field,
        private array | string $values
    ) {
    }

    public function getRules(): array
    {
        return ["required_if:{$this->field},{$this->normalizeValue($this->values)}"];
    }
}
