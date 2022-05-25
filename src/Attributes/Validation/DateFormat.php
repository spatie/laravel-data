<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateFormat extends StringValidationAttribute
{
    public function __construct(private string $format)
    {
    }

    public static function keyword(): string
    {
        return 'date_format';
    }

    public function parameters(): array
    {
        return [$this->format];
    }
}
