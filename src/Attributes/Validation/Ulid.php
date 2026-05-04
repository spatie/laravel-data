<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Ulid extends StringValidationAttribute
{
    public function __construct(
        array|string|null $context = null,
    ) {
        $this->context = $context;
    }

    public static function keyword(): string
    {
        return 'ulid';
    }

    public function parameters(): array
    {
        return [];
    }
}
