<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateFormat implements ValidationAttribute
{
    public function __construct(private string $format)
    {
    }

    public function getRules(): array
    {
        return ['date_format:' . $this->format];
    }
}
