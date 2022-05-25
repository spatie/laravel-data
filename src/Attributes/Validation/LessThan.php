<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class LessThan extends StringValidationAttribute
{
    public function __construct(private string $field)
    {
    }

    public static function keyword(): string
    {
        return 'lt';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
