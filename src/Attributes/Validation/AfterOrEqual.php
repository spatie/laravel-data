<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AfterOrEqual implements ValidationAttribute
{
    public function __construct(private string $date)
{}

    public function getRules(): array
    {
        return ['after_or_equal:' . $this->date];
    }
}
