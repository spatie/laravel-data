<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use DateTimeInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateEquals extends StringValidationAttribute
{
    use GenericRule;

    public function __construct(private string|DateTimeInterface $date)
    {
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
