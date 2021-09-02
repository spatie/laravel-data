<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayType implements ValidationAttribute
{
    public function __construct(private array $keys = [])
    {
    }

    public function getRules(): array
    {
        return empty($this->keys)
            ? ['array']
            : ['array:' . implode(',', $this->keys)];
    }
}
