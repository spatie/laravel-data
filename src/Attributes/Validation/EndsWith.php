<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EndsWith extends ValidationAttribute
{
    private string|array $values;

    public function __construct(string | array ...$values)
    {
        $this->values = $values;
    }

    public function getRules(): array
    {
        return ["ends_with:{$this->normalizeValue(Arr::flatten($this->values))}"];
    }
}
