<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class LessThan extends StringValidationAttribute
{
    public function __construct(protected string $field)
    {
    }

    public static function keyword(): string
    {
        return 'lt';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
