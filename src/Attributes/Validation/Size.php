<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Size extends ValidationAttribute
{
    public function __construct(private int $size)
    {
    }

    public function getRules(): array
    {
        return ["size:{$this->size}"];
    }
}
