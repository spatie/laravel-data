<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Nullable extends StringValidationAttribute
{
    public function __construct(
        array|string|null $context = null,
    ) {
        $this->context = $context;
    }

    public static function keyword(): string
    {
        return 'nullable';
    }

    public function parameters(): array
    {
        return [];
    }
}
