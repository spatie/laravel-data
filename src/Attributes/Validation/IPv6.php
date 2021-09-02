<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IPv6 implements ValidationAttribute
{
    public function getRules(): array
    {
        return ['ipv6'];
    }
}
