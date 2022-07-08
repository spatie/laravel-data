<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use DateTimeInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BeforeOrEqual extends StringValidationAttribute
{
    public function __construct(protected string | DateTimeInterface $date)
    {
    }

    public static function keyword(): string
    {
        return 'before_or_equal';
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
