<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotRegex extends StringValidationAttribute
{
    public function __construct(protected string $pattern)
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
