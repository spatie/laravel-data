<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ActiveUrl implements ValidationAttribute
{
    public function getRules(): array
    {
        return ['active_url'];
    }
}
