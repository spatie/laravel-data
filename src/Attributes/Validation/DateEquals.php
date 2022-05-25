<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Carbon\Carbon;
use DateTimeInterface;
use Spatie\LaravelData\Support\Validation\ValidationRule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateEquals extends StringValidationAttribute
{
    public function __construct(private string | DateTimeInterface $date)
    {
    }

    public static function keyword(): string
    {
        return 'date_equals';
    }

    public function parameters(): array
    {
        return [$this->normalizeValue($this->date)];
    }

    public static function create(string ...$parameters): static
    {
        return parent::create(
            self::parseDateValue($parameters[0])
        );
    }
}
