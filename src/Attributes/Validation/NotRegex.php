<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotRegex extends StringValidationAttribute
{
    public function __construct(private string $pattern)
    {
    }

    public static function keyword(): string
    {
        return 'not_regex';
    }

    public function parameters(): array
    {
        return [$this->pattern];
    }
}
