<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredIf extends ValidationAttribute
{
    private string|array $values;

    public function __construct(
        private string $field,
        array | string ...$values
    ) {
        $this->values = Arr::flatten($values);
    }

    public function getRules(): array
    {
        return ["required_if:{$this->field},{$this->normalizeValue($this->values)}"];
    }
}
