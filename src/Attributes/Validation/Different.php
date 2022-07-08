<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Different extends StringValidationAttribute
{
    public function __construct(protected string $field)
    {
    }

    public static function keyword(): string
    {
        return 'different';
    }

    public function parameters(): array
    {
        return [$this->field];
    }
}
