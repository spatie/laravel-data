<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Regex implements ValidationAttribute
{
    public function __construct(private string $pattern)
    {
    }

    public function getRules(): array
    {
        return ['regex:' . $this->pattern];
    }
}
