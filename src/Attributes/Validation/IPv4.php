<?php

namespace Spatie\LaravelData\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IPv4 extends ValidationAttribute
{
    public function getRules(): array
    {
        return ['ipv4'];
    }
}
