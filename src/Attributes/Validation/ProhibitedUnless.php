<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ProhibitedUnless extends ValidationAttribute
{
    private string|array $values;

    public function __construct(
        private string $field,
        array | string ...$values
    ) {
        $this->values = Arr::flatten($values);
        ;
    }

    public function getRules(): array
    {
        return ["prohibited_unless:{$this->field},{$this->normalizeValue($this->values)}"];
    }
}
