<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DigitsBetween extends StringValidationAttribute
{
    public function __construct(protected int $min, protected int $max)
    {
    }

    public static function keyword(): string
    {
        return 'digits_between';
    }

    public function parameters(): array
    {
        return [$this->min, $this->max];
    }
}
