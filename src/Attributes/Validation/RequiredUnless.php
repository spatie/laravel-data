<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use BackedEnum;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\References\FieldReference;
use Spatie\LaravelData\Support\Validation\RequiringRule;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RequiredUnless extends StringValidationAttribute implements RequiringRule
{
    protected FieldReference $field;

    protected string|array $values;

    public function __construct(
        string|FieldReference $field,
        array|string|BackedEnum ...$values
    ) {
        $this->field = $this->parseFieldReference($field);
        $this->values = Arr::flatten($values);
    }


    public static function keyword(): string
    {
        return 'required_unless';
    }

    public function parameters(): array
    {
        return [
            $this->field,
            $this->values,
        ];
    }
}
