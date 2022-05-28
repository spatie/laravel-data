<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AcceptedIf extends StringValidationAttribute
{
    use GenericRule;

    public function __construct(private string $field, private string|bool|int|float $value)
    {
    }

    public function parameters(): array
    {
        $value = $this->value;

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        return [
            $this->field,
            $value,
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
