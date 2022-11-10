<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use BackedEnum;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AcceptedIf extends StringValidationAttribute
{
    public function __construct(protected string $field, protected string|bool|int|float|BackedEnum $value)
    {
    }

    public static function keyword(): string
    {
        return 'accepted_if';
    }

    public function parameters(): array
    {
        return [
            $this->field,
            self::normalizeValue($this->value),
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
