<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use DateTimeInterface;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BeforeOrEqual extends ValidationAttribute
{
    public function __construct(private string | DateTimeInterface $date)
    {
    }

    public function getRules(): array
    {
        return ["before_or_equal:{$this->normalizeValue($this->date)}"];
    }
}
