<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\FieldReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class LessThan extends StringValidationAttribute
{
    protected int|float|FieldReference $field;

    public function __construct(
        int|float|string|FieldReference $field,
    ) {
        $this->field = is_numeric($field) ? $field : $this->parseFieldReference($field);
    }


    public static function keyword(): string
    {
        return 'lt';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
