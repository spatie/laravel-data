<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BeforeOrEqual implements ValidationAttribute
{
    public function __construct(private string $date)
{}

    public function getRules(): array
    {
        return ['before_or_equal:' . $this->date];
    }
}
