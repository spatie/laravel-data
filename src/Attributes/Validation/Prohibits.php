<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\References\FieldReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Prohibits extends StringValidationAttribute
{
    protected array $fields;

    public function __construct(array|string|FieldReference ...$fields)
    {
        foreach (Arr::flatten($fields) as $field) {
            $this->fields[] = $field instanceof FieldReference ? $field : new FieldReference($field);
        }
    }

    public static function keyword(): string
    {
        return 'prohibits';
    }

    public function parameters(): array
    {
        return [
            $this->fields,
        ];
    }
}
