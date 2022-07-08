<?php

namespace Spatie\LaravelData\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MapInputName
{
    public function __construct(public string|int $input)
    {
    }
}
