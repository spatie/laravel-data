<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use BackedEnum;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;
use Spatie\LaravelData\Support\Validation\References\FieldReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class ExcludeIf extends StringValidationAttribute
{
    protected FieldReference $field;

    public function __construct(
        string|FieldReference $field,
        protected string|int|float|bool|BackedEnum|ExternalReference $value
    ) {
        $this->field = $this->parseFieldReference($field);
    }

    public static function keyword(): string
    {
        return 'exclude_if';
    }

    public static function create(string ...$parameters): static
    {
        return parent::create(
            $parameters[0],
            self::parseBooleanValue($parameters[1]),
        );
    }

    public function parameters(): array
    {
        return [
            $this->field,
            $this->value,
        ];
    }
}
