<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Max implements DataValidationAttribute
{
    public function __construct(public int $length)
    {
    }

    public function getRules(): array
    {
        return ["max:{$this->length}"];
    }
}
