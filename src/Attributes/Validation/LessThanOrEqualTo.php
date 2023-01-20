<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use BackedEnum;
use Spatie\LaravelData\Support\Validation\References\FieldReference;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class LessThanOrEqualTo extends StringValidationAttribute
{
    protected FieldReference $field;

    public function __construct(
        string|FieldReference $field,
    ) {
        $this->field = $this->parseFieldReference($field);
    }


    public static function keyword(): string
    {
        return 'lte';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
