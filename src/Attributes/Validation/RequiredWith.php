<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Support\Arr;
use Spatie\LaravelData\Support\Validation\References\FieldReference;
use Spatie\LaravelData\Support\Validation\RequiringRule;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class RequiredWith extends StringValidationAttribute implements RequiringRule
{
    protected array $fields;

    public function __construct(
        array|string|FieldReference ...$fields,
    ) {
        $extracted = $this->extractContextFromVariadicValues($fields);

        foreach (Arr::flatten($extracted['values']) as $field) {
            $this->fields[] = $field instanceof FieldReference ? $field : new FieldReference($field);
        }

        $this->context = $extracted['context'];
    }

    public static function keyword(): string
    {
        return 'required_with';
    }

    public function parameters(): array
    {
        return [
            $this->fields,
        ];
    }
}
