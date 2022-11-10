<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use BackedEnum;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcludeUnless extends StringValidationAttribute
{
    public function __construct(protected string $field, protected string | bool | int | float | BackedEnum $value)
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
