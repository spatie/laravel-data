<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Spatie\LaravelData\Support\Validation\References\ExternalReference;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Min extends StringValidationAttribute
{
    public function __construct(
        protected int|ExternalReference $value,
        array|string|null $context = null,
    ) {
        $this->context = $context;
    }

    public static function keyword(): string
    {
        return 'min';
    }

    public function parameters(): array
    {
        return [$this->value];
    }
}
