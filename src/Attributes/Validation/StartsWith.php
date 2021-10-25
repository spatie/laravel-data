<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StartsWith extends ValidationAttribute
{
    private string|array $values;

    public function __construct(string | array ...$values)
    {
        $this->values = Arr::flatten($values);
    }

    public function getRules(): array
    {
        return ["starts_with:{$this->normalizeValue($this->values)}"];
    }
}
