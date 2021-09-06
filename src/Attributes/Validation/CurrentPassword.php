<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class CurrentPassword extends ValidationAttribute
{
    public function __construct(private ?string $guard = null)
    {
    }

    public function getRules(): array
    {
        return $this->guard === null
            ? ['current_password']
            : ["current_password:{$this->guard}"];
    }
}
