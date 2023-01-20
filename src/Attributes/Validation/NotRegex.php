<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

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

    public function parameters(ValidationPath $path): array
    {
        return [$this->pattern];
    }
}
