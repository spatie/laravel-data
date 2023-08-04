<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use BackedEnum;
use Spatie\LaravelData\Support\Validation\References\FieldReference;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ExcludeUnless extends StringValidationAttribute
{
    protected FieldReference $field;

    public function __construct(
        string|FieldReference                                              $field,
        protected string|int|float|bool|BackedEnum|RouteParameterReference $value
    ) {
        $this->field = $this->parseFieldReference($field);
    }


    public static function keyword(): string
    {
        return 'exclude_unless';
    }

    public function parameters(): array
    {
        return [
            $this->field,
            $this->value,
        ];
    }
}
