<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MultipleOf extends StringValidationAttribute
{
    public function __construct(protected int | float $value)
    {
    }

    public static function keyword(): string
    {
        return 'multiple_of';
    }

    public function parameters(ValidationPath $path): array
    {
        return [$this->value];
    }
}
