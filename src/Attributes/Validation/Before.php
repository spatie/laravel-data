<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Before implements ValidationAttribute
{
    public function __construct(private string $date)
    {
    }

    public function getRules(): array
    {
        return ['before:' . $this->date];
    }
}
