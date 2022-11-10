<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use BackedEnum;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ProhibitedUnless extends StringValidationAttribute
{
    protected string|array $values;

    public function __construct(
        protected string $field,
        array|string|BackedEnum ...$values
    ) {
        $this->values = Arr::flatten($values);
    }

    public static function keyword(): string
    {
        return 'prohibited_unless';
    }

    public function parameters(): array
    {
        return [
            $this->field,
            $this->normalizeValue($this->values),
        ];
    }
}
