<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Size extends StringValidationAttribute
{
    public function __construct(private int $size)
    {
    }

    public static function keyword(): string
    {
        return 'size';
    }

    public function parameters(): array
    {
        return [$this->size];
    }
}
