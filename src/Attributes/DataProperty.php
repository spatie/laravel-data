<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DataProperty
{
    public function __construct(public readonly ?string $getter = null, public readonly ?string $setter = null)
    {
    }
}
