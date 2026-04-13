<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\FieldReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ExcludeWithout extends StringValidationAttribute
{
    protected FieldReference $field;

    public function __construct(
        string|FieldReference $field,
        array|string|null $context = null,
    ) {
        $this->field = $this->parseFieldReference($field);
        $this->context = $context;
    }

    public static function keyword(): string
    {
        return 'exclude_without';
    }

    public function parameters(): array
    {
        return [
            $this->field,
        ];
    }
}
