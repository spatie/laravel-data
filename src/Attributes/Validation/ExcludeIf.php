<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use BackedEnum;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ExcludeIf extends StringValidationAttribute
{
    public function __construct(protected string $field, protected string|int|float|bool|BackedEnum $value)
    {
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
            $this->normalizeValue($this->value),
        ];
    }
}
