<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;
use Illuminate\Validation\Rules\Password as BasePassword;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PasswordDefaults implements ValidationAttribute
{
    public function getRules(): array
    {
        return [ BasePassword::default() ];
    }
}
