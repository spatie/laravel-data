<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class CurrentPassword extends StringValidationAttribute
{
    public function __construct(private ?string $guard = null)
    {
    }

    public static function keyword(): string
    {
        return 'current_password';
    }

    public function parameters(): array
    {
        return $this->guard === null
            ? []
            : [$this->guard];
    }
}
