<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class InArray extends StringValidationAttribute
{
    public function __construct(private string $field)
    {
    }

    public static function keyword(): string
    {
        return 'in_array';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
