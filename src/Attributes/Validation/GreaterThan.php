<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GreaterThan extends StringValidationAttribute
{
    public function __construct(private string $field)
    {
    }

    public static function keyword(): string
    {
        return 'gt';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
