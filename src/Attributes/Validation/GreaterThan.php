<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\FieldReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class GreaterThan extends StringValidationAttribute
{
    protected FieldReference|int|float $field;

    public function __construct(
        int|float|string|FieldReference $field,
        array|string|null $context = null,
    ) {
        $this->field = is_numeric($field) ? $field : $this->parseFieldReference($field);
        $this->context = $context;
    }

    public static function keyword(): string
    {
        return 'gt';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
