<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayType extends ValidationAttribute
{
    private array $keys;

    public function __construct(array|string ...$keys)
    {
        $this->keys = $keys;
    }

    public function getRules(): array
    {
        return empty($this->keys)
            ? ['array']
            : ['array:' . implode(',', Arr::flatten($this->keys))];
    }
}
