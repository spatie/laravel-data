<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GreaterThanOrEqualTo extends StringValidationAttribute
{
    use GenericRule;
    public function __construct(private string $field)
    {
    }

    public static function keyword(): string
    {
        return 'gte';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
