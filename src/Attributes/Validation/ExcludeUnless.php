<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcludeUnless extends StringValidationAttribute
{
    public function __construct(protected string $field, protected string | bool | int | float $value)
    {
    }

    public static function keyword(): string
    {
        return 'exclude_unless';
    }

    public function parameters(): array
    {
        return [
            $this->field,
            $this->normalizeValue($this->value),
        ];
    }
}
