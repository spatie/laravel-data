<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\RequiringRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredUnless extends StringValidationAttribute implements RequiringRule
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
        return 'required_unless';
    }

    public function parameters(): array
    {
        return [
            $this->field,
            $this->normalizeValue($this->values),
        ];
    }
}
