<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Min extends StringValidationAttribute
{
    public function __construct(private int $value)
    {
    }

    public static function keyword(): string
    {
        return 'min';
    }

    public function parameters(): array
    {
        return [$this->value];
    }
}
