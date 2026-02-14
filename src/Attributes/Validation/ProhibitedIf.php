<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use BackedEnum;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use Spatie\LaravelData\Support\Validation\References\FieldReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ProhibitedIf extends StringValidationAttribute
{
    protected FieldReference $field;

    protected string|array $values;

    public function __construct(
        string|FieldReference $field,
        array|string|BackedEnum|ExternalReference ...$values,
    ) {
        $extracted = $this->extractContextFromVariadicValues($values);

        $this->field = $this->parseFieldReference($field);
        $this->values = Arr::flatten($extracted['values']);
        $this->context = $extracted['context'];
    }

    public static function keyword(): string
    {
        return 'prohibited_if';
    }

    public function parameters(): array
    {
        return [
            $this->field,
            $this->values,
        ];
    }
}
