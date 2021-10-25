<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Prohibits extends ValidationAttribute
{
    private string|array $fields;

    public function __construct(array | string ...$fields)
    {
        $this->fields = Arr::flatten($fields);
    }

    public function getRules(): array
    {
        return ["prohibits:{$this->normalizeValue($this->fields)}"];
    }
}
