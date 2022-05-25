<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use DateTimeInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Before extends StringValidationAttribute
{
    public function __construct(private string | DateTimeInterface $date)
    {
    }

    public static function keyword(): string
    {
        return 'before';
    }

    public function parameters(): array
    {
        return [$this->normalizeValue($this->date)];
    }

    public static function create(string ...$parameters): static
    {
        return parent::create(
            self::parseDateValue($parameters[0]),
        );
    }
}
