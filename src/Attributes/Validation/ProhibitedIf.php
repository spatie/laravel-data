<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ProhibitedIf extends StringValidationAttribute
{
    private string|array $values;

    public function __construct(
        private string $field,
        array | string ...$values
    ) {
        $this->values = Arr::flatten($values);
    }

    public static function keyword(): string
    {
        return 'prohibited_if';
    }

    public function parameters(): array
    {
        return [
            $this->field,
            $this->normalizeValue($this->values)
        ];
    }
}
