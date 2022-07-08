<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Regex extends StringValidationAttribute
{
    public function __construct(protected string $pattern)
    {
    }

    public static function keyword(): string
    {
        return 'regex';
    }

    public function parameters(): array
    {
        return [
            $this->normalizeValue($this->pattern),
        ];
    }
}
