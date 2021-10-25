<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredWithAll extends ValidationAttribute
{
    private string|array $fields;

    public function __construct(
        array | string ...$fields,
    ) {
        $this->fields = Arr::flatten($fields);
    }

    public function getRules(): array
    {
        return ["required_with_all:{$this->normalizeValue($this->fields)}"];
    }
}
