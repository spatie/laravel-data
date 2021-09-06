<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IPv6 extends ValidationAttribute
{
    public function getRules(): array
    {
        return ['ipv6'];
    }
}
