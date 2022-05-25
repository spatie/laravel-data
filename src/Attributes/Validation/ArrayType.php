<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayType extends StringValidationAttribute
{
    private array $keys;

    public function __construct(array|string ...$keys)
    {
        $this->keys = Arr::flatten($keys);
    }

    public static function keyword(): string
    {
        return 'array';
    }

    public function parameters(): array
    {
        return $this->keys;
    }
}
