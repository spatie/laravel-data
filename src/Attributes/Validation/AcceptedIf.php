<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use BackedEnum;
use Spatie\LaravelData\Support\Validation\References\FieldReference;
use Spatie\LaravelData\Support\Validation\References\RouteParameterReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class AcceptedIf extends StringValidationAttribute
{
    protected FieldReference $field;

    public function __construct(
        string|FieldReference $field,
        protected string|bool|int|float|BackedEnum|RouteParameterReference $value
    ) {
        $this->field = $this->parseFieldReference($field);
    }

    public static function keyword(): string
    {
        return 'accepted_if';
    }

    public function parameters(): array
    {
        return [
            $this->field,
            $this->value,
        ];
    }

    public static function create(string ...$parameters): static
    {
        return parent::create(
            $parameters[0],
            self::parseBooleanValue($parameters[1])
        );
    }
}
