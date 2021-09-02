<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use DateTimeInterface;
use Spatie\LaravelData\Attributes\Validation\Concerns\BuildsValidationRules;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BeforeOrEqual implements ValidationAttribute
{
    use BuildsValidationRules;

    public function __construct(private string | DateTimeInterface $date)
    {
    }

    public function getRules(): array
    {
        return ["before_or_equal:{$this->normalizeValue($this->date)}"];
    }
}
