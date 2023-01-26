<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Same extends StringValidationAttribute
{
    public function __construct(protected string | RouteParameterReference $field)
    {
    }

    public static function keyword(): string
    {
        return 'same';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
