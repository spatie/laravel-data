<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute]
class Max implements DataValidationAttribute
{
    public function __construct(public int $maxLength)
    {
    }

    public function getRules(): array
    {
        return ["max:{$this->maxLength}"];
    }
}
