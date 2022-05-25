<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Digits extends StringValidationAttribute
{
    public function __construct(private int $value)
    {
    }

    public static function keyword(): string
    {
        return 'digits';
    }

    public function parameters(): array
    {
        return [$this->value];
    }
}
