<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ArrayType extends StringValidationAttribute
{
    protected array $keys;

    public function __construct(array|string|ExternalReference ...$keys)
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
