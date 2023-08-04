<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\FieldReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Different extends StringValidationAttribute
{
    protected FieldReference $field;

    public function __construct(string|FieldReference $field)
    {
        $this->field = $this->parseFieldReference($field);
    }

    public static function keyword(): string
    {
        return 'different';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
