<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ProhibitedIf extends StringValidationAttribute
{
    use GenericRule;
    private string|array $values;

    public function __construct(
        private string $field,
        array | string ...$values
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
