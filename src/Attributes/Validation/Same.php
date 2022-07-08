<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Same extends StringValidationAttribute
{
    public function __construct(protected string $field)
    {
    }

    public static function keyword(): string
    {
        return 'same';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
