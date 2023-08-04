<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class DateFormat extends StringValidationAttribute
{
    public function __construct(protected string $format)
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
