<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Size extends StringValidationAttribute
{
    public function __construct(protected int | RouteParameterReference $size)
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
