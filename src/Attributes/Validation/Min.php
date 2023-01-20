<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\ValidationPath;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Min extends StringValidationAttribute
{
    public function __construct(protected int $value)
    {
    }

    public static function keyword(): string
    {
        return 'min';
    }

    public function parameters(ValidationPath $path): array
    {
        return [$this->value];
    }
}
