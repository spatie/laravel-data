<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\RequiringRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredWith extends StringValidationAttribute implements RequiringRule
{
    use GenericRule;

    private string|array $fields;

    public function __construct(
        array | string ...$fields,
    ) {
        $this->fields = Arr::flatten($fields);
    }

    public function parameters(): array
    {
        return [
            $this->normalizeValue($this->fields),
        ];
    }
}
