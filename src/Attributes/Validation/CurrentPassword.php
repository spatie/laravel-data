<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class CurrentPassword extends StringValidationAttribute
{
    use GenericRule;

    public function __construct(private ?string $guard = null)
    {
    }

    public function parameters(): array
    {
        return $this->guard === null
            ? []
            : [$this->guard];
    }
}
