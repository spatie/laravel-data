<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\RequiringRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredIf extends StringValidationAttribute implements RequiringRule
{
    use GenericRule;

    private string|array $values;

    public function __construct(
        private string $field,
        array|string ...$values
    ) {
        $this->values = Arr::flatten($values);
    }

    public function parameters(): array
    {
        return [
            $this->field,
            $this->normalizeValue($this->values),
        ];
    }
}
