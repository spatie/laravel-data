<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateEquals implements ValidationAttribute
{
    public function __construct(private string $date)
{}

    public function getRules(): array
    {
        return ['date_equals:' . $this->date];
    }
}
